<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Barangmasuk;
use App\Models\Barang;
use Illuminate\Support\Facades\Storage;

class BarangmasukController extends Controller
{
    public function index()
    {
        // Mengambil data dari tabel 'barangmasuk' menggunakan Eloquent ORM dengan relasi ke 'barang'
        $barangmasuk = Barangmasuk::with('barang')->latest()->paginate(10);

        return view('v_barangmasuk.index', compact('barangmasuk'));
    }

    public function create()
    {
        $merkBarang = Barang::pluck('merk', 'id');
        // Menampilkan form untuk membuat data barang masuk
        return view('v_barangmasuk.create', compact('merkBarang'));
    }

    public function store(Request $request)
    {
        // Validasi data dari request jika diperlukan
        $validatedData = $request->validate([
            'tgl_masuk' => 'required',
            'qty_masuk' => 'required|numeric|min:0',
            'barang_id' => 'required',
            // Tambahkan validasi lainnya sesuai kebutuhan
        ]);

        // Simpan data barang masuk baru ke database
        $barangMasuk = new Barangmasuk();
        $barangMasuk->tgl_masuk = $request->tgl_masuk;
        $barangMasuk->qty_masuk = $request->qty_masuk;
        $barangMasuk->barang_id = $request->barang_id;
        // Tambahkan kolom lainnya yang perlu diisi
        $barangMasuk->save();

        return redirect()->route('barangmasuk.index')->with('success', 'Barang masuk berhasil ditambahkan');
    }

    public function show($id)
    {
        // Mengambil data barang masuk berdasarkan ID
        $barangmasuk = Barangmasuk::findOrFail($id);
    
        return view('v_barangmasuk.show', compact('barangmasuk'));
    }

    public function edit($id)
    {
        // Mengambil data barang masuk untuk diedit berdasarkan ID
        $barangmasuk = Barangmasuk::findOrFail($id);
        $merkBarang = Barang::pluck('merk', 'id');
    
        return view('v_barangmasuk.edit', compact('barangmasuk', 'merkBarang'));
    }
    
    public function update(Request $request, $id)
    {
        // Validasi data dari request jika diperlukan
        $validatedData = $request->validate([
            'tgl_masuk' => 'required',
            'qty_masuk' => 'required|numeric|min:0',
            // Tambahkan validasi lainnya sesuai kebutuhan
        ]);
    
        // Temukan data barang masuk yang akan diupdate
        $barangMasuk = BarangMasuk::findOrFail($id);
    
        // Hitung perbedaan antara stok yang baru dan yang sebelumnya
        $difference = $request->qty_masuk - $barangMasuk->qty_masuk;
    
        // Simpan perubahan data barang masuk ke database
        $barangMasuk->tgl_masuk = $request->tgl_masuk;
        $barangMasuk->qty_masuk = $request->qty_masuk;
        $barangMasuk->save();
    
        // Update stok barang
        $barang = Barang::find($request->barang_id);
    
        if ($difference > 0) {
            // Jika perbedaan positif, tambahkan ke stok
            $barang->stok += $difference;
        } elseif ($difference < 0) {
            // Jika perbedaan negatif, kurangi dari stok
            $barang->stok -= abs($difference);
        }
    
        $barang->save();
    
        return redirect()->route('barangmasuk.index')->with('success', 'Barang masuk berhasil diperbarui');
    }
    
    
    public function destroy($id)
    {
        // Menghapus data barang masuk berdasarkan ID
        $barangmasuk = Barangmasuk::findOrFail($id);
        $barangmasuk->delete();
    
        return redirect()->route('barangmasuk.index')->with('success', 'Barang masuk berhasil dihapus');
    }
    

}