FROM wordpress:latest

RUN apt-get update && \
    apt-get install -y \
        less \
        curl \
        sudo \
        nano \
        && rm -rf /var/lib/apt/lists/*

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
