<?php

namespace Database\Seeders;

use App\Models\Feature;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $features = [
      ['code' => 'RAB_TEMPLATE', 'name' =>  'Template perhitungan RAB'],
      ['code' => 'ITEM_PRICE_REFERENCE', 'name' =>  'Referensi harga satuan'],
      ['code' => 'AHSP_PUPR_2024_REFERENCE', 'name' =>  'Referensi AHSP Permen PUPR 2024'],
      ['code' => 'RAB_EXCEL_EXPORT', 'name' =>  'Export perhitungan RAB dalam bentuk file Excel'],
      ['code' => 'RAB_LPSE_EXPORT', 'name' =>  'Export perhitungan RAB dalam bentuk file Excel APENDO LPSE'],
      ['code' => 'SIMPLE_RAB_REFERENCE', 'name' =>  'Referensi RAB sederhana'],
      ['code' => 'CREATE_IMPLEMENTATION_SCHEDULE', 'name' =>  'Pembuatan Jadwal Pelaksanaan'],
      ['code' => 'CREATE_AUTOMATED_SCURVE', 'name' =>  'Pembuatan Kurva S secara otomatis'],
      ['code' => 'CALCULATE_HUMAN_RESOURCE_NEEDS', 'name' =>  'Menghitung jumlah kebutuhan Tenaga Kerja'],
      ['code' => 'CALCULATE_MATERIAL_NEEDS', 'name' =>  'Menghitung jumlah kebutuhan Material'],
      ['code' => 'CALCULATE_TOOLS_NEEDS', 'name' =>  'Menghitung jumlah kebutuhan Peralatan'],
      ['code' => 'IMPORT_CUSTOM_RAB_EXCEL', 'name' =>  'Upload uraian pekerjaan dan volume menggunakan template'],
      ['code' => 'PERSONAL_SUPPORT_TEAM', 'name' =>  'Personal Tim Support untuk diskusi RAB Kamu']
    ];
    foreach ($features as $index => $feature) {
      Feature::updateOrInsert(
        ['code' => $feature['code']],
        [
          'name' => $feature['name'],
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]
      );
    }
  }
}
