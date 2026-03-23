FROM yiisoftware/yii2-php:7.2-apache

# Change document root for Apache
RUN sed -i -e 's|/app/web|/app/backend/web|g' /etc/apache2/sites-available/000-default.conf

# Fix permissions for runtime directories
RUN mkdir -p /app/backend/runtime/export-jobs && \
    chown -R www-data:www-data /app/backend/runtime && \
    chmod -R 755 /app/backend/runtime

# รัน composer install เมื่อ container เริ่ม ถ้ายังไม่มี vendor (แก้ 500 จาก require autoload.php)
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]