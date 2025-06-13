Penerapan Turing Machine: The Busy Beaver Problem

---

Pengantar
The Busy Beaver Problem adalah masalah menarik dalam ilmu komputer teoretis yang berhubungan erat dengan Turing Machine (Mesin Turing). Diperkenalkan oleh Tibor Rado pada tahun 1962, masalah ini menyelami batas-batas komputasi, memberikan wawasan mendalam tentang algoritma, dan konsep ketidakmampuan untuk memutuskan (undecidability).

---

🦫 The Busy Beaver Problem
The Busy Beaver Problem berfokus pada pencarian Mesin Turing paling "sibuk" atau "produktif" untuk sejumlah keadaan (state) tertentu. Secara spesifik, masalah ini mencari Mesin Turing dengan $n$ state yang:
1.  Berhenti (halt) setelah sejumlah langkah.
2.  Mencetak jumlah simbol '1' terbanyak pada pita (tape) awal yang kosong, sebelum berhenti.

Mesin Turing yang memenuhi kriteria ini disebut Busy Beaver untuk $n$ state tersebut.

✨ Premis Dasar dan Konsep
Untuk memahami The Busy Beaver Problem, kita perlu memahami dasar Mesin Turing:
* Pita (Tape): Pita tak terbatas yang terbagi menjadi sel, berisi simbol '0' (kosong) atau '1'.
* Kepala Baca/Tulis (Read/Write Head): Membaca/menulis simbol dan bergerak ke kiri/kanan.
* Keadaan (State): Kondisi internal Mesin Turing (contoh: A, B, C, ...). Ada juga keadaan berhenti (Halt State).
* Fungsi Transisi: Aturan `(state_saat_ini, simbol_dibaca) -> (simbol_ditulis, arah_gerak, state_berikutnya)`.

🎯 Tujuan The Busy Beaver Problem adalah menemukan dua nilai untuk setiap $n$:
1.  $\Sigma(n)$: Jumlah maksimum simbol '1' yang dicetak.
2.  $S(n)$: Jumlah maksimum langkah yang diambil.

$\Sigma(n)$ dikenal sebagai fungsi Busy Beaver. Ini seperti kompetisi di mana mesin yang berhenti dengan '1' terbanyak adalah pemenangnya.

🤖 Ilustrasi
Bayangkan Anda memiliki sejumlah robot kecil (n robot, mewakili n state) di atas sebuah jalur panjang (pita). Setiap robot dapat membaca apa yang ada di depannya, menulis sesuatu, dan kemudian memutuskan untuk bergerak ke kiri atau kanan, dan mengubah kondisi internalnya. The Busy Beaver Problem meminta Anda untuk merancang aturan untuk robot-robot ini sehingga, dimulai dari jalur kosong, mereka akhirnya berhenti, dan ketika mereka berhenti, ada sebanyak mungkin tanda '1' di jalur tersebut.

🌳 Contoh dan Implementasi Pemenang (hingga 3 State)

Menemukan Busy Beaver sangat sulit karena jumlah kemungkinan Mesin Turing meningkat secara eksponensial. Ini juga terkait dengan halting problem (masalah berhenti) yang tidak dapat diputuskan.

* `0`: Sel kosong
* `1`: Sel berisi '1'
* `R`: Gerak ke kanan
* `L`: Gerak ke kiri
* `A, B, C`: State kerja
* `H`: State Berhenti/Halt
* Tabel transisi akan ditampilkan dalam format: (state_saat_ini, simbol_dibaca) → (simbol_ditulis, arah_gerak, state_berikutnya).

1️⃣ Busy Beaver untuk $n=1$ State
Mesin Turing 1-state hanya punya satu state kerja (A) dan satu state berhenti (H).

Tabel Transisi:

| Keadaan Saat Ini | Simbol Dibaca | Simbol Ditulis | Arah Gerak | Keadaan Berikutnya |
| :--------------: | :------------: | :-------------: | :----------: | :----------------: |
|        A         |       0        |        1        |      R       |          H         |
|        A         |       1        |        1        |      R       |          H         |

![State1 drawio](https://github.com/user-attachments/assets/7ba10203-bb08-4e00-bcc3-dfc6f7fc19e6)

Simulasi:
1. Tape : ...00[0]00... (Kepala di sel kosong)
2. State A membaca '0'.
3. Menulis '1', bergerak ke kanan (sesuai aturan), dan masuk ke state H (berhenti).
4. Tape: ...00[1]00...
   
Hasil:
* $\Sigma(1) = 1$  (satu simbol '1' dicetak)
* $S(1) = 1$ (satu langkah komputasi)

2️⃣ Busy Beaver untuk $n=2$ States
Mesin Turing 2-state memiliki dua state kerja (A, B) dan satu state berhenti (H).

Tabel Transisi (Pemenang):

| Keadaan Saat Ini | Simbol Dibaca | Simbol Ditulis | Arah Gerak | Keadaan Berikutnya |
| :--------------: | :------------: | :-------------: | :----------: | :----------------: |
|        A         |       0        |        1        |      R       |          B         |
|        A         |       1        |        1        |      L       |          A         |
|        B         |       0        |        1        |      L       |          A         |
|        B         |       1        |        1        |      R       |          H         |


![State2 drawio](https://github.com/user-attachments/assets/83c7d4e4-d358-4c4b-9339-4c91144af877)

Simulasi : <br>
Mulai: Pita ...000**[0]**000..., Keadaan A
1. (A, 0) → (1, R, B): ...001\[0]000..., Keadaan B
2. (B, 0) → (1, L, A): ...011000..., Keadaan A (kembali ke sel '1')
3. (A, 1) → (1, L, A): ...111000..., Keadaan A
4. (A, 1) → (1, L, A): ...111000..., Keadaan A
5. (A, 1) → (1, L, A): ...111000..., Keadaan A (sekarang membaca '0' di kiri)
6. (A, 0) → (1, R, B): ...111100..., Keadaan B
7. (B, 0) → (1, L, A): ...111110..., Keadaan A
8. (A, 1) → (1, R, B): ...111110..., Keadaan B
9. (B, 1) → (1, H, -): Berhenti

Hasil:
* $\Sigma(2) = 3$ (ada tiga '1' di pita sebelum berhenti)
* $S(2) = 6$ (enam langkah komputasi)

*(Catatan: Simulasi detail untuk 2-state ini cukup panjang).*

3️⃣ Busy Beaver untuk $n=3$ States
Mesin Turing 3-state memiliki tiga state kerja (A, B, C) dan satu state berhenti (H).

Tabel Transisi (Pemenang):

| Keadaan Saat Ini | Simbol Dibaca | Simbol Ditulis | Arah Gerak | Keadaan Berikutnya |
| :--------------: | :------------: | :-------------: | :----------: | :----------------: |
|        A         |       0        |        1        |      R       |          B         |
|        A         |       1        |        1        |      L       |          B         |
|        B         |       0        |        1        |      L       |          A         |
|        B         |       1        |        1        |      L       |          C         |
|        C         |       0        |        1        |      R       |          H         |
|        C         |       1        |        0        |      R       |          A         |

![State3 drawio](https://github.com/user-attachments/assets/a00a3282-501a-4535-a223-9fe37437746d)

Simulasi : <br>
(dimulai dari pita kosong ...000... dan kepala di sel tengah, keadaan A): <br>
Simulasi untuk 3 state jauh lebih panjang dan rumit untuk dituliskan secara manual di sini. Busy Beaver 3-state yang dikenal menghasilkan Σ(3)=6 simbol '1' dan membutuhkan S(3)=21 langkah sebelum berhenti.

Hasil:
* $\Sigma(3) = 6$
* $S(3) = 21$

*(Simulasi untuk 3-state sangat kompleks dan panjang untuk ditulis secara detail di sini.)*

Nilai $\Sigma(n)$ dan $S(n)$ tumbuh sangat cepat dan bersifat tidak rekursif, artinya tidak ada algoritma umum untuk menghitungnya untuk semua $n$. Untuk $n \ge 4$, nilai-nilai ini sebagian besar masih menjadi subjek penelitian.