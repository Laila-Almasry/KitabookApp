FROM php:8.2-fpm

# تثبيت الحزم المطلوبة لتشغيل Laravel
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# تثبيت Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# تعيين مجلد العمل داخل الحاوية
WORKDIR /var/www

# نسخ كل الملفات إلى مجلد العمل داخل الحاوية
COPY . .

# تثبيت مكتبات Laravel
RUN composer install --no-dev --optimize-autoloader

# إعطاء صلاحيات للمجلدات المهمة
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# فتح المنفذ 8000
EXPOSE 8000

# أمر التشغيل الأساسي
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
