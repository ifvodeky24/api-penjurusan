<?php

namespace App\Http\Controllers;

use App\Models\Hasil;
use App\Models\HasilDetail;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class HasilController extends Controller
{

    public function insertHasilJawaban(){

        $rawData = json_decode(file_get_contents("php://input"), true);
        $idSiswa = $rawData['id_siswa'];
        $jawaban = $rawData['jawaban'];

        try {
            $hasil = new Hasil();
            $hasil->siswa_id = $idSiswa;
            $hasil->hasil_akhir = "IPA";
            $hasil->save();

            for ($size = 0; $size < sizeof($jawaban); $size++) {
                DB::table('tb_hasil_detail')->insert([
                    'hasil_id' => $hasil['id_hasil'],
                    'jawaban_id' => $jawaban[$size]['jawaban_id']
                ]);
            }

            return response()->json([
                'code' => 200,
                'status' => 'Success',
                'message' => 'SUCCESS',
                'result' => ""
            ], 200);

        }
        catch (\Exception $exception) {
            return response()->json([
                'code' => 500,
                'status' => 'Failed',
                'message' => $exception->getMessage(),
                'result' => ""
            ], 500);
        }

    }

    public function getHasilAll() {

        try {
            $hasil = Hasil::all();

            $data = array();

            foreach ($hasil as $item){
                $array['id_hasil'] = $item->id_hasil;
                $array['hasil_akhir'] = $item->hasil_akhir;
                $array['siswa'] = $this->getSiswaById($item->siswa_id);
                $array['hasil_jawaban'] = $this->getHasilJawaban($item->id_hasil);
                array_push($data, $array);
            }

            return response()->json([
                'code' => 200,
                'status' => "Success",
                'message' => "SUCCESS",
                'result' => $data
            ], 200);
        }
        catch (\Exception $exception) {

            return response()->json([
                'code' => 500,
                'status' => "Success",
                'message' => "SUCCESS",
                'result' => $exception->getMessage()
            ], 500);

        }

    }

    private function getHasilJawaban($hasil_id)
    {
        return HasilDetail::join('tb_jawaban', 'tb_hasil_detail.hasil_id', '=', 'tb_jawaban.id_jawaban')
            ->where('tb_hasil_detail.hasil_id', $hasil_id)->get(['tb_jawaban.jawaban', 'tb_jawaban.skor', 'tb_jawaban.pertanyaan_id', 'tb_hasil_detail.*']);
    }

    private function getSiswaById($siswa_id)
    {
        return Siswa::find($siswa_id);
    }
}
