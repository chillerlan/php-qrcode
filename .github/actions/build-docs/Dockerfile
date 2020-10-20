# https://hub.docker.com/r/phpdoc/phpdoc
FROM phpdoc/phpdoc:3.0

LABEL "repository"="https://github.com/chillerlan/php-qrcode"

LABEL "com.github.actions.name"="Build Docs"
LABEL "com.github.actions.description"="Build Docs with phpDocumentor"
LABEL "com.github.actions.icon"="file-text"
LABEL "com.github.actions.color"="blue"

# don't show errors
RUN echo "display_errors = Off" > $PHP_INI_DIR/conf.d/errors.ini

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
