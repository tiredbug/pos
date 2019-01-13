<?php
class model_transaksi extends ci_model
{
    
    public function buat_kode($value='')
    {
        $this->db->select('RIGHT(transaksi.transaksi_id,4) as kode', FALSE);
          $this->db->order_by('transaksi_id','DESC');    
          $this->db->limit(1);    
          $query = $this->db->get('transaksi');      //cek dulu apakah ada sudah ada kode di tabel.    
          if($query->num_rows() <> 0){      
           //jika kode ternyata sudah ada.      
           $data = $query->row();      
           $kode = intval($data->kode) + 1;    
          }
          else {      
           //jika kode belum ada      
           $kode = 1;    
          }
          $kodemax = str_pad($kode, 4, "0", STR_PAD_LEFT); // angka 4 menunjukkan jumlah digit angka 0
          $ym = date('ym');
          $kodejadi = "ERP-$ym-".$kodemax;    // 
          return $kodejadi;  
    }

    function simpan_barang()
    {
        $transaksi_id = $this->input->post('transaksi_id');
        $id_barang      = $this->input->post('id_barang');
        $nama_barang    =  $this->input->post('barang');
        $qty            =  $this->input->post('qty');
        $idbarang       = $this->db->get_where('barang',array('nama_barang'=>$nama_barang))->row_array();
        $data           = array('transaksi_id'=>$transaksi_id['transaksi_id'],
                                'barang_id'=>$idbarang['barang_id'],
                                'qty'=>$qty,
                                'harga'=>$idbarang['harga'],
                                'status'=>'0');
        $this->db->insert('transaksi_detail',$data);
    }
    
    function tampilkan_detail_transaksi()
    {
        $query  ="SELECT td.t_detail_id,td.qty,td.harga,b.nama_barang
                FROM transaksi_detail as td,barang as b
                WHERE b.barang_id=td.barang_id and td.status='0'";
        return $this->db->query($query);
    }
    
    function hapusitem($id)
    {
        $this->db->where('t_detail_id',$id);
        $this->db->delete('transaksi_detail');
    }
    
    
    function selesai_belanja($data)
    {
        $this->db->insert('transaksi',$data);
        $last_id=  $this->db->query("select transaksi_id from transaksi order by transaksi_id desc")->row_array();
        $this->db->query("update transaksi_detail set transaksi_id='".$last_id['transaksi_id']."' where status='0'");
        $this->db->query("update transaksi_detail set status='1' where status='0'");
    }
    
    
    function laporan_default()
    {
        $query="SELECT t.tanggal_transaksi,o.nama_lengkap,sum(td.harga*td.qty) as total
                FROM transaksi as t,transaksi_detail as td,operator as o
                WHERE td.transaksi_id=t.transaksi_id and o.operator_id=t.operator_id
                group by t.transaksi_id";
        return $this->db->query($query);
    }
    
    function laporan_periode($tanggal1,$tanggal2)
    {
        $query="SELECT t.tanggal_transaksi,o.nama_lengkap,sum(td.harga*td.qty) as total
                FROM transaksi as t,transaksi_detail as td,operator as o
                WHERE td.transaksi_id=t.transaksi_id and o.operator_id=t.operator_id 
                and t.tanggal_transaksi between '$tanggal1' and '$tanggal2'
                group by t.transaksi_id";
        return $this->db->query($query);
    }
}