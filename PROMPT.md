Kamu adalah senior Laravel backend engineer. Eksekusi proyek CariKostKita berdasarkan seluruh dokumentasi Markdown yang tersedia di repository, terutama PRD.md, DESIGN.md, DATABASE.md, API.md, SECURITY.md, DEPLOYMENT.md, ENVIRONMENT.md, dan ROADMAP.md. Jangan hanya menjelaskan atau memberikan contoh kode; langsung implementasikan proyek Laravel yang benar-benar runnable.

Tujuan utama: membangun Laravel REST API sebagai backend tunggal untuk Android App, Landing Page, dan Admin Dashboard CariKostKita. Gunakan PostgreSQL dari Supabase sebagai database utama dan Supabase Storage untuk penyimpanan gambar. Jangan gunakan database lokal/server deployment sebagai database utama.

Kerjakan secara bertahap dan jangan melompati fondasi:
1. Audit repository dan dokumentasi Markdown terlebih dahulu.
2. Buat/rapikan struktur Laravel menggunakan arsitektur yang modular dan maintainable.
3. Konfigurasikan PostgreSQL Supabase melalui environment variables.
4. Buat migrations, models, relationships, factories/seeders seperlunya berdasarkan DATABASE.md.
5. Implementasikan authentication, role pencari/pemilik/admin, authorization dan policies.
6. Implementasikan API kost, kamar, foto, search, filter, favorite, profile, chat dan admin sesuai API.md.
7. Implementasikan integrasi Supabase Storage untuk upload foto dengan validasi file dan ukuran.
8. Gunakan Form Request untuk validasi, API Resource untuk response, Service Layer untuk business logic/integrasi eksternal, dan Policy/Middleware untuk authorization.
9. Terapkan security sesuai SECURITY.md. Jangan expose credential, jangan commit .env, jangan mengembalikan password/secret ke client.
10. Gunakan migration sebagai sumber utama struktur database. Jangan meminta saya membuat tabel secara manual jika dapat dibuat melalui migration.
11. Buat API response yang konsisten sesuai API.md dan gunakan HTTP status code yang tepat.
12. Buat testing untuk authentication, authorization, CRUD kost, kamar, favorite dan endpoint penting lainnya.
13. Tambahkan README.md yang menjelaskan instalasi, konfigurasi .env, migration, seeding, menjalankan server lokal, testing dan deployment.
14. Jangan membuat fitur di luar PRD tanpa alasan yang jelas. Jika ada requirement yang ambigu, pilih solusi Laravel yang paling sederhana dan maintainable lalu dokumentasikan keputusan tersebut.
15. Jangan membuat kode dummy/palsu untuk menggantikan fitur yang seharusnya benar-benar bekerja.
16. Jangan membuat duplikasi controller, model, migration, route, atau implementasi lama. Jika menemukan implementasi lama yang bertentangan dengan desain terbaru, refactor atau hapus setelah memastikan tidak diperlukan.
17. Setelah setiap tahap selesai, jalankan test/lint/check yang relevan dan perbaiki error sebelum lanjut.
18. Jangan berhenti hanya karena konfigurasi Supabase belum tersedia. Buat .env.example dan struktur integrasinya dengan benar, lalu jelaskan credential apa saja yang perlu saya isi.

Prioritas implementasi:
Foundation → Database → Authentication → Role/Authorization → Kost → Kamar → Storage → Search/Filter → Favorite → Chat → Admin → Testing → Documentation.

Aturan penting:
- Laravel harus menjadi satu-satunya API gateway untuk client.
- Android, Landing Page dan Admin Dashboard tidak boleh mengakses PostgreSQL Supabase secara langsung.
- Jangan menaruh Supabase service-role key di frontend/mobile.
- Gunakan environment variables untuk semua credential.
- Document root production harus diarahkan ke folder Laravel /public.
- Gunakan PostgreSQL-compatible migrations.
- Pastikan foreign key, index, unique constraint dan relationship dibuat dengan benar.
- Gunakan pagination untuk endpoint list.
- Validasi ownership sebelum pemilik mengubah/menghapus kost atau kamar.
- Kost baru memiliki status pending dan hanya dapat tampil publik setelah approved oleh admin.
- Buat kode yang siap dikembangkan, bukan sekadar prototype.

Mulai sekarang dengan audit project yang ada. Jika project Laravel belum ada, buat project Laravel terlebih dahulu. Setelah audit, implementasikan Phase 1 dan Phase 2 dari ROADMAP.md terlebih dahulu. Tampilkan ringkasan file yang dibuat/diubah, command yang dijalankan, hasil test, dan langkah berikutnya setelah tahap tersebut benar-benar selesai.