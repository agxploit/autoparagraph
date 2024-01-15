<?php
/*
Plugin Name: Auto Paragraph
Description: Mengedepankan user friendly pada blog WordPress dengan membuat paragraf otomatis dari setiap kalimat yang diakhiri dengan tanda baca titik.
Version: 1.0
Author: Plato Rich
License: MIT
*/
function auto_paragraph( $content ) {
    // Memeriksa apakah halaman atau post artikel
    if ( is_singular('post') ) {
        // Membuat daftar tag-heading yang ingin dikecualikan
        $excluded_tags = array( 'h2', 'h3', 'h4', 'h5', 'h6' );

        // Membuat pola regex untuk menangkap tag-heading yang dikecualikan
        $pattern = '/<\/?(h2|h3|h4|h5|h6)[^>]*>/';

        // Membuat daftar pola-pola pengecualian tambahan
        $exceptions = array(
            '/\d\d:\d\d:\d\./', // Tanda titik pada jam
            '/\b(?:Dr\.|Mr\.|Mrs\.|Ms\.)\b/', // Gelar
            '/[A-Z]\./', // Tanda titik pada poin artikel
            '/\b[A-Z][a-z]+\.\b/', // Nama dengan gelar (contoh: John D.)
            '/\b\d+\b/', // Nomor urut
        );

        // Memisahkan kalimat-kalimat
        $sentences = explode( ".", $content );

        // Array untuk menyimpan kalimat yang telah diolah
        $processed_sentences = array();

        // Menambahkan penanda untuk memeriksa apakah kalimat sebelumnya telah ditutup dengan </p>
        $previous_closed = true;

        // Mengolah setiap kalimat
        foreach ( $sentences as $sentence ) {
            // Hapus spasi di awal dan akhir kalimat
            $sentence = trim( $sentence );

            // Tambahkan titik di akhir kalimat jika belum ada
            if ( substr( $sentence, -1 ) !== '.' && ! preg_match( '/<\/a>/', $sentence ) ) {
                $sentence .= '.';
            }

            // Hapus tanda titik ganda pada poin
            $sentence = preg_replace('/\.\./', '.', $sentence);

            // Tambahkan tag <p> hanya jika bukan tag-heading
            if ( ! preg_match( $pattern, $sentence ) ) {
                // Pengecekan pengecualian
                $exception_matched = false;
                foreach ( $exceptions as $exception_pattern ) {
                    if ( preg_match( $exception_pattern, $sentence ) ) {
                        $exception_matched = true;
                        break;
                    }
                }

                if ( ! $exception_matched ) {
                    // Jika kalimat sebelumnya belum ditutup dengan </p>, tutup kalimat sebelumnya
                    if ( ! $previous_closed ) {
                        $processed_sentences[] = "</p>";
                        $previous_closed = true;
                    }

                    // Cek apakah kalimat dimulai dengan huruf dan diikuti oleh tanda titik dan spasi
                    if ( preg_match('/^(\w+\.\s*)(.*)/', $sentence, $matches) ) {
                        // Jika ya, tambahkan tag <p> dan masukkan teks di bawah nomor
                        $processed_sentences[] = "<p>{$matches[1]}{$matches[2]}</p>";
                        $previous_closed = true;
                    } else {
                        // Jika bukan poin, tambahkan tag <p>
                        $processed_sentences[] = "<p>{$sentence}</p>";
                        $previous_closed = false;
                    }
                } else {
                    $processed_sentences[] = $sentence;
                }
            } else {
                // Jika tag-heading, biarkan tanpa perubahan
                // Tambahkan spasi setelah nomor pada tag-heading
                $sentence = preg_replace('/(\w+\.)/', '$1 ', $sentence);
                $processed_sentences[] = $sentence;
                $previous_closed = false;
            }
        }

        // Tutup kalimat terakhir dengan </p> jika belum ditutup
        if ( ! $previous_closed ) {
            $processed_sentences[] = "</p>";
        }

        // Gabungkan kembali kalimat-kalimat
        $content = implode( '', $processed_sentences );
    }

    return $content;
}

// Menambahkan filter untuk memproses konten sebelum ditampilkan
add_filter( 'the_content', 'auto_paragraph' );

/*
MIT License

Copyright (c) [Tahun] [Nama Pemilik Hak Cipta]

Lisensi ini memberikan izin tanpa batasan kepada setiap orang yang mendapatkan salinan
dari perangkat lunak ini dan file dokumentasi terkait ("Perangkat Lunak"), untuk diperdagangkan
tanpa batasan, termasuk tanpa batasan hak untuk menggunakan, menyalin, memodifikasi,
menggabungkan, mempublikasikan, mendistribusikan, mensublisensikan, dan/atau menjual
salinan dari Perangkat Lunak, dan untuk mengizinkan orang yang menerima itu untuk
melakukannya, dengan ketentuan bahwa pemberitahuan di atas dan paragraf ini disertakan dalam
semua salinan atau bagian yang signifikan dari Perangkat Lunak.
*/

?>
