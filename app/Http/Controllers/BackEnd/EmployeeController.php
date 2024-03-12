<?php

namespace App\Http\Controllers\BackEnd;

use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class EmployeeController extends Controller
{

    //TODO: unused func, will be deleted 
    public function getEmployeesData()
    {
        // $employeesLNJ2 = DB::connection('mysql')->table('mhpegawai')->take(20)->get();
        $employeesLNJ2 = DB::connection('mysql')->table('mhpegawai')->get();

        $successCount = 0;
        foreach ($employeesLNJ2 as $employeeData) {

            $employee = [
                'id' => $employeeData->nomor,
                'code' => $employeeData->nik ?? '',
                'id_card_num' => $employeeData->ktp ?? null,
                'tax_account_num' => $employeeData->npwp ?? '',
                'name' => $employeeData->nama ?? '',
                'nickname' => $employeeData->nama_panggilan ?? '',
                'work_started_at' => $employeeData->tanggal_mulai_kerja ?? '0000-00-00',
                'branch_id' => $employeeData->nomormhcabang ?? '',
                'department_id' => $employeeData->department_id ?? null,
                'sub_department_id' => $employeeData->nomormhsubdivisi ?? '',
                'position_id' => $employeeData->nomormhposisi ?? '',
                'born_city_id' => $employeeData->tempat_lahir ?? '',
                'date_of_birth' => $employeeData->tanggal_lahir ?? '0000-00-00',
                'gender' => $employeeData->jenis_kelamin ?? '',
                'marriage_status' => $employeeData->status_keluarga ?? '',
                'address' => $employeeData->alamat_jalan ?? '',
                'neighborhood_unit' => $employeeData->alamat_rt ?? '',
                'community_unit' => $employeeData->alamat_rw ?? '',
                'address_city_id' => $employeeData->nomormhkota ?? '',
                'domicile' => $employeeData->alamat_domicile ?? '',
                'domicile_id' => $employeeData->nama ?? '',
                'name' => $employeeData->nama ?? '',
                'phone_num' => $employeeData->nomor_telepon ?? '',
                'mobile_num_1' => $employeeData->nomor_hp_1 ?? '',
                'mobile_num_2' => $employeeData->nomor_hp_2 ?? '',
                'emergency_num' => $employeeData->nomor_darurat ?? '',
                'emergency_name' => $employeeData->nama_nomor_darurat ?? '',
                'number_of_children' => $employeeData->jumlah_anak ?? '',
                'remark' => $employeeData->keterangan ?? '',
                'is_active' => $employeeData->status_aktif == 1 ? true : false,
            ];


            $result = Employee::updateOrInsert(['id' => $employee['id']], $employee);

            if ($result) {
                $successCount++;
            }
        }

        if ($successCount == count($employeesLNJ2)) {

            Setting::where('code', 'last_sync')->update([
                'value' => now(),
                'updated_by' => auth()->user()->id,
            ]);

            return $this->sendResponse(
                true,
                Response::HTTP_OK,
                true
            );
        } else {

            return $this->sendResponse(
                false,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                false
            );
        }
    }

    public function retreiveEmployees()
    {
        //$dataFromA = DB::connection('mysql')->table('mhpegawai')->get();
        $dataFromA = DB::connection('mysql')
            ->table('mhpegawai')
            // ->where('nomor', '>=', 450)
            ->get();



        Setting::where('code', 'employee')->update([
            'value' => now(),
            'updated_at' => now(),
            'updated_by' => auth()->user()->id,
        ]);

        // TODO:@wahyu compare sama data last updated sync lnj. jika lebih besar maka upsert. 
        // compare sama selectnya LNJ2
        // Jika lebih besar maka di upsert, jika tidak ada tidak perlu ngapa2in.


        foreach ($dataFromA as $data) {

            try {
                // Coba untuk mengonversi tanggal_mulai_kerja ke dalam format yang diinginkan
                $parsedDate = Carbon::parse($data->tanggal_mulai_kerja);

                // Pemeriksaan tambahan untuk tanggal yang kurang dari '2000-01-01'
                if ($parsedDate->lessThan('2000-01-01')) {
                    throw new \Exception('Invalid work started date. Date is less than 2000-01-01.');
                }

                $workStartedAt = $parsedDate->format('Y-m-d');
            } catch (\Exception $e) {
                // Tangani kesalahan jika parsing gagal atau tanggal kurang dari '2000-01-01'
                // Misalnya, beri nilai default atau tampilkan pesan kesalahan kepada pengguna
                $workStartedAt = '2000-01-01';
                // Tampilkan pesan kesalahan atau lakukan tindakan lain sesuai kebutuhan                
            }

            try {
                // Coba untuk mengonversi tanggal_lahir ke dalam format yang diinginkan
                $parsedDate2 = Carbon::parse($data->tanggal_lahir);

                // Pemeriksaan tambahan untuk tanggal yang kurang dari '2000-01-01'
                if ($parsedDate2->lessThan('2000-01-01')) {
                    throw new \Exception('Invalid birth date. Date is less than 2000-01-01.');
                }

                $birthDate = $parsedDate2->format('Y-m-d');
            } catch (\Exception $e) {
                // Tangani kesalahan jika parsing gagal atau tanggal kurang dari '2000-01-01'
                // Misalnya, beri nilai default atau tampilkan pesan kesalahan kepada pengguna
                $birthDate = '2000-01-01';
                // Tampilkan pesan kesalahan atau lakukan tindakan lain sesuai kebutuhan                
            }

            $employeeData = [
                'id' => $data->nomor,
                'code' => $data->nik ?? '',
                'id_card_num' => $data->ktp ?? null,
                'tax_account_num' => $data->npwp ?? '',
                'name' => $data->nama ?? '',
                'nickname' => $data->nama_panggilan ?? '',
                'work_started_at' => $workStartedAt == '0000-00-00' ? '2000-01-01' : $workStartedAt,
                // 'branch_id' => $data->nomormhcabang ?? '',
                // 'department_id' => $data->department_id ?? null,
                // 'sub_department_id' => $data->nomormhsubdivisi ?? '',
                // 'position_id' => $data->nomormhposisi ?? '',
                // 'born_city_id' => $data->tempat_lahir ?? '',
                'date_of_birth' => $birthDate == '0000-00-00' ? '2000-01-01' : $birthDate,
                'gender' => $data->jenis_kelamin ?? '',
                'marriage_status' => $data->status_keluarga ?? '',
                'address' => $data->alamat_jalan ?? '',
                'neighborhood_unit' => $data->alamat_rt ?? '',
                'community_unit' => $data->alamat_rw ?? '',
                // 'address_city_id' => $data->nomormhkota ?? '',
                'domicile' => $data->alamat_domicile ?? '',
                // 'domicile_id' => $data->nama ?? '',
                'name' => $data->nama ?? '',
                'phone_num' => $data->nomor_telepon ?? '',
                'mobile_num_1' => $data->nomor_hp_1 ?? '',
                'mobile_num_2' => $data->nomor_hp_2 ?? '',
                'emergency_num' => $data->nomor_darurat ?? '',
                'emergency_name' => $data->nama_nomor_darurat ?? '',
                'number_of_children' => $data->jumlah_anak ?? '',
                'remark' => $data->keterangan ?? '',
                'is_active' => $data->status_aktif == 1 ? true : false,
            ];

            // $employee = Employee::create($employeeData);
            $result = Employee::updateOrInsert(['id' => $employeeData['id']], $employeeData);


            // $diseasesData = [];

            // Mapping untuk alergi
            if ($data->is_alergi == 1) {
                $employeeId = $data->nomor;
                $name = 'alergi';
                $remark = $data->deskripsi_alergi;

                DB::connection('pgsql')->table('employee_disease')->updateOrInsert(
                    ['employee_id' => $employeeId, 'name' => $name],
                    ['remark' => $remark]
                );
            }

            // Mapping untuk rawat rs
            if ($data->is_rawat_rs == 1) {
                DB::connection('pgsql')->table('employee_disease')->updateOrInsert(
                    ['employee_id' => $data->nomor, 'name' => 'Rawat RS'],
                    ['remark' => '']
                );
            }

            // Mapping untuk operasi
            if ($data->is_operasi == 1) {
                DB::connection('pgsql')->table('employee_disease')->updateOrInsert(
                    ['employee_id' => $data->nomor, 'name' => 'Operasi'],
                    ['remark' => '']
                );
            }


            if ($data->is_diabetes == 1) {

                DB::connection('pgsql')->table('employee_disease')->updateOrInsert(
                    ['employee_id' => $data->nomor, 'name' => 'Diabetes'],
                    ['remark' => '']
                );
            }

            if ($data->is_jantung == 1) {

                DB::connection('pgsql')->table('employee_disease')->updateOrInsert(
                    ['employee_id' => $data->nomor, 'name' => 'Jantung'],
                    ['remark' => '']
                );
            }

            if ($data->is_kanker == 1) {

                DB::connection('pgsql')->table('employee_disease')->updateOrInsert(
                    ['employee_id' => $data->nomor, 'name' => 'Kanker'],
                    ['remark' => '']
                );
            }

            if ($data->is_pingsan == 1) {

                DB::connection('pgsql')->table('employee_disease')->updateOrInsert(
                    ['employee_id' => $data->nomor, 'name' => 'Pingsan'],
                    ['remark' => '']
                );
            }

            if ($data->is_asma == 1) {

                DB::connection('pgsql')->table('employee_disease')->updateOrInsert(
                    ['employee_id' => $data->nomor, 'name' => 'Asma'],
                    ['remark' => '']
                );
            }

            if ($data->is_hipertensi == 1) {

                DB::connection('pgsql')->table('employee_disease')->updateOrInsert(
                    ['employee_id' => $data->nomor, 'name' => 'Hipertensi'],
                    ['remark' => '']
                );
            }

            if ($data->is_anemia == 1) {

                DB::connection('pgsql')->table('employee_disease')->updateOrInsert(
                    ['employee_id' => $data->nomor, 'name' => 'Anemia'],
                    ['remark' => '']
                );
            }

            if ($data->riwayat_lain != null && $data->riwayat_lain != '') {

                DB::connection('pgsql')->table('employee_disease')->updateOrInsert(
                    ['employee_id' => $data->nomor, 'name' => 'Riwayat lain'],
                    ['remark' => '']
                );
            }

            // Simpan penyakit-penyakit yang sudah di-mapping
            // Note: kenapa relasinya ga berjalan ??
            // $employee->employeeDiseases()->createMany($diseasesData);
        }



        return $this->sendResponse(
            true,
            Response::HTTP_OK,
            true
        );
    }
}
