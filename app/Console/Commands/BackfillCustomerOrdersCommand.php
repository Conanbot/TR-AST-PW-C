<?php
namespace App\Console\Commands;
use App\Services\CustomerService;
use Illuminate\Console\Command;
class BackfillCustomerOrdersCommand extends Command
{
    protected $signature = 'customers:backfill-orders
                            {--dry-run : Jalankan simulasi tanpa mengubah data di database}
                            {--apply : Terapkan perubahan ke database}';
    protected $description = 'Hubungkan order lama yang belum memiliki customer_id ke master customer berdasarkan nomor telepon ternormalisasi.';
    public function handle(CustomerService $customerService): int
    {
        $isApply = (bool) $this->option('apply');
        if (!$isApply) {
            $this->warn('Menjalankan dalam mode DRY-RUN. Tidak ada data yang diubah di database.');
            $this->line('Gunakan flag --apply jika ingin menerapkan perubahan ke database.');
        } else {
            $this->info('Menjalankan dalam mode APPLY. Data customer dan order_id akan diperbarui.');
        }
        $result = $customerService->backfillOrders($isApply);
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Mode Eksekusi', strtoupper($result['mode'])],
                ['Total Order Tanpa customer_id', $result['total_found']],
                ['Order Dengan Telepon Valid', $result['valid_phones']],
                ['Order Dilewati (Telepon Kosong/Tidak Valid)', $result['invalid_phones']],
                ['Customer Terdaftar Cocok', $result['existing_customers_matched']],
                ['Customer Baru ' . ($isApply ? 'Dibuat' : 'Akan Dibuat'), $result['new_customers_created']],
                ['Order ' . ($isApply ? 'Berhasil Dihubungkan' : 'Akan Dihubungkan'), $result['orders_linked']],
            ]
        );
        if (!$isApply) {
            $this->info('Simulasi dry-run selesai. Jalankan `php artisan customers:backfill-orders --apply` saat database staging telah terverifikasi.');
        } else {
            $this->info('Backfill customer orders berhasil diterapkan.');
        }
        return self::SUCCESS;
    }
}
