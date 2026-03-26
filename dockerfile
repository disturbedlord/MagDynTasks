# Use official PHP Apache image
FROM php:8.2-apache

# Copy your code to the container
COPY . /var/www/html/

# Enable Apache rewrite (if needed)
RUN a2enmod rewrite

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]