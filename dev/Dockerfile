FROM wordpress:latest

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

# Set the working directory
WORKDIR /var/www/html

# Run WP-CLI as a non-root user
USER www-data

# Print WP-CLI version
RUN wp --info
