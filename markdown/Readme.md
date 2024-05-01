# Benchmark results

## Environment

| Name | Value |
|------|-------|
| date | 2024-05-01 09:55:31 |
| environment | Linux #19~22.04.2-Ubuntu SMP Thu Mar 21 16:45:46 UTC 2024, x86_64 |
| tag | &lt;current&gt; |
| php_version | 8.3.6 |
| php_ini | /etc/php/8.3/cli/php.ini |
| php_extensions | Core, date, libxml, openssl, pcre, zlib, filter, hash, json, pcntl, random, Reflection, SPL, session, standard, sodium, mysqlnd, PDO, xml, amqp, apcu, ast, bcmath, bz2, calendar, ctype, curl, dba, dom, enchant, mbstring, FFI, fileinfo, ftp, gd, gettext, gmp, iconv, igbinary, imagick, imap, intl, ldap, exif, memcache, mongodb, msgpack, mysqli, odbc, pdo_dblib, PDO_Firebird, pdo_mysql, PDO_ODBC, pdo_pgsql, pdo_sqlite, pdo_sqlsrv, pgsql, Phar, posix, pspell, readline, redis, shmop, SimpleXML, snmp, soap, sockets, sqlite3, sqlsrv, sysvmsg, sysvsem, sysvshm, tidy, tokenizer, xmlreader, xmlwriter, xsl, yaml, zip, zmq, memcached, ds, Zend OPcache |
| php_xdebug | ✗ |
| opcache_extension_loaded | ✓ |
| opcache_enabled | ✗ |

## Reports

- [QRDataBenchmark](./QRDataBenchmark.md)
  - [invocation](./QRDataBenchmark.md#invocation)
  - [writeMatrix](./QRDataBenchmark.md#writematrix)
  - [decodeSegment](./QRDataBenchmark.md#decodesegment)
- [DecoderBenchmark](./DecoderBenchmark.md)
  - [GDLuminanceSource](./DecoderBenchmark.md#gdluminancesource)
  - [IMagickLuminanceSource](./DecoderBenchmark.md#imagickluminancesource)
- [MaskPatternBenchmark](./MaskPatternBenchmark.md)
  - [getBestPattern](./MaskPatternBenchmark.md#getbestpattern)
- [QRCodeBenchmark](./QRCodeBenchmark.md)
  - [render](./QRCodeBenchmark.md#render)
- [OutputBenchmark](./OutputBenchmark.md)
  - [QREps](./OutputBenchmark.md#qreps)
  - [QRFpdf](./OutputBenchmark.md#qrfpdf)
  - [QRGdImageAVIF](./OutputBenchmark.md#qrgdimageavif)
  - [QRGdImageJPEG](./OutputBenchmark.md#qrgdimagejpeg)
  - [QRGdImagePNG](./OutputBenchmark.md#qrgdimagepng)
  - [QRGdImageWEBP](./OutputBenchmark.md#qrgdimagewebp)
  - [QRImagick](./OutputBenchmark.md#qrimagick)
  - [QRMarkupSVG](./OutputBenchmark.md#qrmarkupsvg)
  - [QRStringJSON](./OutputBenchmark.md#qrstringjson)