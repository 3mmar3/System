# تحليل مشروع System (نظرة شاملة)

## 1) ملخص سريع
هذا المشروع عبارة عن **لوحة داخلية للموظفين** مبنية بـ PHP (نمط ملفات مباشرة بدون إطار عمل)، وتجمع بين:
- تسجيل الحضور والانصراف والبريك.
- طلبات الإجازات.
- إدارة الحجوزات (أساسي + تفاصيل حسب نوع الحجز).
- إدارة المستخدمين والأدوار والصلاحيات.
- إعدادات الواجهة والتبويبات.
- مهام To-Do.

> قاعدة البيانات الأساسية: `u125244766_system`.

---

## 2) المعمارية الحالية (Architecture)

### نمط التطبيق
- **Monolithic PHP Pages**: كل صفحة/ملف PHP يتعامل مباشرة مع قاعدة البيانات + واجهة HTML + منطق الأعمال.
- لا يوجد فصل كامل بين طبقة العرض ومنطق الأعمال وطبقة البيانات.

### الاتصال بقاعدة البيانات
يوجد طريقتان للاتصال معًا داخل نفس المشروع:
1. `config.php` باستخدام **MySQLi**.
2. `db.php` باستخدام **PDO**.

هذا يسبب عدم اتساق في الكود، ويصعّب الصيانة والاختبار.

### الجلسات والأمان
- استخدام `session_start()` في أغلب الملفات.
- تسجيل الدخول مع `password_verify` (جيد).
- يوجد توليد CSRF token في `config.php`، لكن التطبيق غير موحّد في استخدامه بكل النماذج.

---

## 3) الوحدات الوظيفية (Modules)

### أ) المصادقة وإدارة المستخدمين
- `login.php`, `logout.php`, `reset_password.php`
- `user-management.php`, `add_user.php`, `delete_user.php`, `get_users.php`, `get_user_list.php`

الهدف: دخول المستخدمين، إدارة الحسابات، تعيين الدور.

### ب) الأدوار والصلاحيات
- `roles.php`, `add_role.php`, `edit_role.php`, `update_role.php`, `delete_role.php`, `get_roles.php`, `get_permissions.php`

الهدف: إدارة Role-based access بشكل يدوي مع تخزين الصلاحيات نصيًا داخل جدول `roles`.

### ج) الحضور والانصراف
- `attendance.php`, `save_attendance.php`, `get_attendance_data.php`, `get_day_info.php`, `get_user_status.php`
- `run_auto_end.php` و `run_auto_end1.php` (إغلاق تلقائي غالبًا عبر cron)

الهدف: تسجيل start/end/break_start/break_end وحسابات شهرية.

### د) الإجازات
- `leave_request.php`, `leave_request_api.php`, `get_leave_requests.php`, `update_leave_status.php`

الهدف: تقديم طلب إجازة ومراجعته واعتماده/رفضه.

### هـ) إدارة الحجوزات
- الصفحة الرئيسية: `my-reservations.php`
- جلب/عرض: `fetch_booking.php`, `fetch-reservation.php`, `delete.php`
- التقارير: `admin_reports.php` (وأيضًا `reports.php` لكن يحتاج مراجعة).

النمط: جدول رئيسي `bookings` + جداول تفاصيل حسب النوع (hotel/flight/visa/...).

### و) الإعدادات والتنقل
- `settings.php`, `edit_tab.php`, `tabs` في DB

الهدف: تخصيص التبويبات والروابط حسب الدور.

### ز) مهام الموظف
- `to-do-list.php` يعتمد على `todo_items`.

---

## 4) تحليل قاعدة البيانات (Database Understanding)

## الجداول الأساسية

### 4.1 المستخدمون والصلاحيات
- `users`: بيانات الحساب (username, password hash, role).
- `roles`: اسم الدور + permissions (نص/JSON نصي غالبًا).
- `user_emails`: بريد إضافي مرتبط بالمستخدم.

### 4.2 التشغيل اليومي
- `attendance`: سجل أحداث الحضور.
- `leave_requests`: طلبات الإجازات وحالتها.
- `todo_items`: مهام كل مستخدم.

### 4.3 الحجوزات
- `bookings`: رأس الحجز (بيانات العميل + أرقام مالية + الحالة).
- جداول تفصيلية مرتبطة بـ `booking_id`:
  - `hotel_bookings`
  - `flight_bookings`
  - `visa_bookings`
  - `appointment_bookings`
  - `insurance_bookings`
  - `entertainment_bookings`
  - `transportation_bookings`
  - `cruise_bookings`

### 4.4 إعدادات النظام
- `settings`: شعار، عنوان الموقع، timezone...
- `tabs`: عناصر القائمة الجانبية (تدعم parent/child).
- `status_options`: خيارات حالة (مرجعية).

---

## 5) العلاقات الأساسية (ER Snapshot)

- `bookings (1) -> (N) [type-specific tables]` عبر `booking_id`.
- `users (1) -> (N) bookings` عبر `user_id`.
- `users (1) -> (N) user_emails`.
- `tabs (self reference)` عبر `parent_id`.

ملحوظة: ليست كل الجداول مربوطة بقيود FK كاملة، وبعضها يعتمد على ترابط منطقي بالكود.

---

## 6) نقاط قوة
- المشروع يغطي احتياجات تشغيلية حقيقية (HR + Reservations).
- وجود جداول مفصّلة لكل نوع حجز يسهّل توسيع الحقول حسب الخدمة.
- استخدام `password_hash/password_verify` أفضل من التخزين الصريح لكلمات المرور.

---

## 7) مشاكل تقنية حالية (مهم)
1. **وجود بيانات اعتماد قاعدة البيانات داخل الكود** مباشرة.
2. **خلط PDO و MySQLi** في نفس المشروع.
3. **تكرار منطق الصلاحيات/الجلسات** في ملفات كثيرة.
4. **بعض الاستعلامات المباشرة** بدون تحضير كامل (قابلة لتحسين الأمان).
5. **الملف `reports.php` يبدو غير متسق** مع باقي المخطط (يعتمد على جداول لا تظهر في dump الحالي مثل booking_statuses/booking_types/functions.php).
6. غياب طبقة API واضحة أو Service Layer.

---

## 8) ترقية قاعدة البيانات (اقتراح عملي)

### المرحلة 1 (سريعة وآمنة)
- نقل إعدادات الاتصال إلى `.env`.
- توحيد الاتصال على PDO فقط.
- إضافة Indexes على:
  - `attendance(username, action_time)`
  - `leave_requests(username, leave_date, status)`
  - `bookings(user_id, created_at, status)`
- توحيد أسماء الأعمدة الزمنية (`created_at`, `updated_at`) عبر كل الجداول.

### المرحلة 2 (تحسين البنية)
- ربط `users.role_id` فعليًا بجدول `roles.id` وإلغاء الاعتماد النصي على `users.role` تدريجيًا.
- نقل permissions من نص خام إلى JSON مضبوط أو جدول علاقات `role_permissions`.
- إنشاء جداول مرجعية للـ enums (status/type) وتقليل القيم الحرة.

### المرحلة 3 (قابلية التوسع)
- إدخال Migration Tool (Phinx أو Laravel migrations).
- إنشاء طبقة Repository/Service.
- إضافة Audit Log للأحداث الحساسة (تعديل صلاحيات، حذف حجز، تغيير حالة إجازة).

---

## 9) خطة فهم سريعة لأي مطور جديد
1. ابدأ بـ `login.php` ثم `dashboard.php`.
2. تتبع الحضور من `attendance.php` إلى `save_attendance.php`.
3. تتبع الحجوزات من `my-reservations.php` إلى `fetch_booking.php` ثم جداول التفاصيل.
4. راقب إدارة الصلاحيات من `roles.php` + `get_permissions.php`.
5. افهم التخصيص من `settings.php` وجدول `tabs`.

---

## 10) الخلاصة
المشروع **عملي ومفيد** لكنه يحتاج **تنظيم تقني** أكثر من حاجته لإعادة كتابة كاملة.
أفضل نتيجة بأقل مخاطرة: **توحيد طبقة DB + تشديد الأمان + تنظيم الصلاحيات + خطة migrations**.

إذا رغبت، أقدر في خطوة لاحقة أجهّز لك:
- ERD مرتب بصيغة Mermaid.
- ملف SQL migration أولي للفهارس والعلاقات الناقصة.
- Roadmap تنفيذ أسبوع-أسبوع للترقية بدون تعطيل التشغيل.
