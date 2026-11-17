<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChickenCut;
use App\Models\DrinkOption;
use App\Models\MenuItem;
use App\Models\Promo;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GeprekGloTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed initial store settings and admin
        StoreSetting::current();
        User::create([
            'name' => 'Admin Geprek GLO',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
        ]);
    }

    /**
     * T-01: Navigasi menu semua halaman publik
     */
    public function test_public_pages_load_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('GEPREK');

        $response = $this->get('/profil');
        $response->assertStatus(200);
        $response->assertSee('Ibu Catur Legawaningsih');

        $response = $this->get('/menu');
        $response->assertStatus(200);
        $response->assertSee('Daftar Menu');

        $response = $this->get('/kontak');
        $response->assertStatus(200);
        $response->assertSee('Blotongan');
    }

    /**
     * Test JSON API contract (Bab 4 PRD)
     */
    public function test_api_menu_and_settings_contract(): void
    {
        $category = Category::create(['name' => 'Paket Hemat', 'slug' => 'paket-hemat']);
        MenuItem::create([
            'category_id' => $category->id,
            'name' => 'Paket Ayam Crispy',
            'price' => 11000,
            'is_available' => true,
        ]);

        $resMenu = $this->getJson('/api/menu');
        $resMenu->assertStatus(200)
            ->assertJsonStructure([
                'categories' => [
                    '*' => ['id', 'name', 'slug', 'items']
                ],
                'chicken_cuts',
                'drink_options'
            ]);

        $resSettings = $this->getJson('/api/settings');
        $resSettings->assertStatus(200)
            ->assertJsonStructure([
                'is_open',
                'wa_number',
                'address',
                'opening_hours',
                'packaging_fee',
                'store_lat',
                'store_lng',
                'delivery_enabled',
                'delivery_radius_m',
            ]);
    }

    /**
     * T-11: Akses /admin tanpa login dialihkan ke /admin/login
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    /**
     * T-12: Login admin valid dan tidak valid
     */
    public function test_admin_login_authentication(): void
    {
        // Salah
        $failResponse = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);
        $failResponse->assertSessionHasErrors('username');
        $this->assertGuest();

        // Benar
        $successResponse = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);
        $successResponse->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    /**
     * T-15: SQL Injection pada login ditolak
     */
    public function test_sql_injection_on_login_is_prevented(): void
    {
        $response = $this->post('/admin/login', [
            'username' => "' OR '1'='1' --",
            'password' => "' OR '1'='1'",
        ]);
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /**
     * T-03: Toggle status kedai Buka/Tutup oleh admin
     */
    public function test_admin_can_toggle_store_status(): void
    {
        $admin = User::first();
        $this->actingAs($admin);

        $settings = StoreSetting::current();
        $this->assertTrue($settings->is_open);

        $res = $this->patchJson('/admin/store/toggle');
        $res->assertStatus(200)
            ->assertJson(['success' => true, 'is_open' => false]);

        $this->assertFalse(StoreSetting::current()->is_open);
    }

    /**
     * T-04: Toggle stok menu Tersedia/Habis oleh admin
     */
    public function test_admin_can_toggle_menu_item_availability(): void
    {
        $admin = User::first();
        $this->actingAs($admin);

        $cat = Category::create(['name' => 'Aneka Ayam', 'slug' => 'aneka-ayam']);
        $item = MenuItem::create([
            'category_id' => $cat->id,
            'name' => 'Ayam Geprek',
            'price' => 10000,
            'is_available' => true,
        ]);

        $res = $this->patchJson("/admin/items/{$item->id}/availability");
        $res->assertStatus(200)
            ->assertJson(['success' => true, 'is_available' => false]);

        $item->refresh();
        $this->assertFalse($item->is_available);
    }

    /**
     * Toggle stok potongan ayam dan opsi minuman
     */
    public function test_admin_can_toggle_cut_and_drink_availability(): void
    {
        $admin = User::first();
        $this->actingAs($admin);

        $cut = ChickenCut::create(['name' => 'Dada', 'is_available' => true]);
        $resCut = $this->patchJson("/admin/cuts/{$cut->id}/availability");
        $resCut->assertStatus(200)->assertJson(['success' => true, 'is_available' => false]);

        $drink = DrinkOption::create(['name' => 'Es Teh', 'is_available' => true]);
        $resDrink = $this->patchJson("/admin/drinks/{$drink->id}/availability");
        $resDrink->assertStatus(200)->assertJson(['success' => true, 'is_available' => false]);
    }

    /**
     * T-06 & T-07: Aturan biaya kemasan Takeaway vs Dine-In (BR-01, BR-02)
     */
    public function test_store_packaging_fee_settings(): void
    {
        $settings = StoreSetting::current();
        $this->assertEquals(1000, $settings->packaging_fee);

        $subtotal = 22000;
        $takeawayFee = $settings->packaging_fee;
        $totalTakeaway = $subtotal + $takeawayFee;
        $totalDineIn = $subtotal; // Rp0 additional

        $this->assertEquals(23000, $totalTakeaway);
        $this->assertEquals(22000, $totalDineIn);
    }

    /**
     * T-13 & T-14: Upload foto validasi ukuran maksimal 2MB dan MIME image
     */
    public function test_menu_item_image_upload_validation(): void
    {
        $admin = User::first();
        $this->actingAs($admin);
        Storage::fake('public');

        $cat = Category::create(['name' => 'Aneka Ayam', 'slug' => 'aneka-ayam']);

        // T-13: Upload berkas teks/php berbahaya bukan gambar
        $badFile = UploadedFile::fake()->create('malicious.php', 100, 'text/x-php');
        $resBad = $this->post('/admin/items', [
            'category_id' => $cat->id,
            'name' => 'Menu Uji',
            'price' => 10000,
            'image' => $badFile,
        ]);
        $resBad->assertSessionHasErrors('image');

        // T-14: Upload gambar melebihi 2MB (2048 KB)
        $oversizedImage = UploadedFile::fake()->image('huge.jpg')->size(2500);
        $resLarge = $this->post('/admin/items', [
            'category_id' => $cat->id,
            'name' => 'Menu Uji Besar',
            'price' => 10000,
            'image' => $oversizedImage,
        ]);
        $resLarge->assertSessionHasErrors('image');

        // Valid image
        $validImage = UploadedFile::fake()->image('food.jpg', 400, 300)->size(500);
        $resValid = $this->post('/admin/items', [
            'category_id' => $cat->id,
            'name' => 'Menu Uji Valid',
            'price' => 10000,
            'image' => $validImage,
        ]);
        $resValid->assertRedirect('/admin/items');
        $this->assertDatabaseHas('menu_items', ['name' => 'Menu Uji Valid']);
    }

    /**
     * Admin CRUD promo banner
     */
    public function test_admin_can_crud_promos(): void
    {
        $admin = User::first();
        $this->actingAs($admin);

        // Create
        $resCreate = $this->post('/admin/promos', [
            'title' => 'Promo Diskon Mahasiswa',
            'subtitle' => 'Tunjukkan KTM untuk dapat diskon',
            'is_active' => 1,
        ]);
        $resCreate->assertRedirect('/admin/promos');
        $this->assertDatabaseHas('promos', ['title' => 'Promo Diskon Mahasiswa']);

        $promo = Promo::where('title', 'Promo Diskon Mahasiswa')->first();

        // Update
        $resUpdate = $this->put("/admin/promos/{$promo->id}", [
            'title' => 'Promo Mahasiswa Update',
            'subtitle' => 'Keterangan baru',
            'is_active' => 0,
        ]);
        $resUpdate->assertRedirect('/admin/promos');
        $this->assertDatabaseHas('promos', ['title' => 'Promo Mahasiswa Update', 'is_active' => false]);

        // Delete
        $resDelete = $this->delete("/admin/promos/{$promo->id}");
        $resDelete->assertRedirect('/admin/promos');
        $this->assertDatabaseMissing('promos', ['id' => $promo->id]);
    }

    /**
     * Admin update store settings (FR-24)
     */
    public function test_admin_can_update_store_settings(): void
    {
        $admin = User::first();
        $this->actingAs($admin);

        $res = $this->put('/admin/settings', [
            'is_open' => 1,
            'wa_number' => '6281234567890',
            'address' => 'Blotongan Baru No. 12',
            'opening_hours' => 'Setiap Hari: 08:00 - 22:00 WIB',
            'packaging_fee' => 1500,
            'maps_url' => 'https://maps.google.com/test',
            'review_url' => 'https://maps.google.com/review',
            'store_lat' => -7.3117,
            'store_lng' => 110.4851,
            'delivery_enabled' => 0,
            'delivery_radius_m' => 800,
        ]);

        $res->assertRedirect('/admin/settings');
        $this->assertDatabaseHas('store_settings', [
            'wa_number' => '6281234567890',
            'packaging_fee' => 1500,
        ]);
    }
}
