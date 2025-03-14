# Benchmark results

## Environment

| Name | Value |
|------|-------|
| date | 2025-03-14 19:18:00 |
| environment | Linux #25-Ubuntu SMP Wed Jan 15 20:45:09 UTC 2025, x86_64 |
| tag | &lt;current&gt; |
| php_version | 8.4.4 |
| php_ini | /etc/php/8.4/cli/php.ini |
| php_extensions | Core, date, libxml, openssl, pcre, zlib, filter, hash, json, pcntl, random, Reflection, SPL, session, standard, sodium, mysqlnd, PDO, xml, amqp, apcu, ast, bcmath, bz2, calendar, ctype, curl, dba, dom, enchant, mbstring, FFI, fileinfo, ftp, gd, gettext, gmp, iconv, igbinary, imagick, imap, intl, ldap, exif, memcache, mongodb, msgpack, mysqli, odbc, pdo_dblib, PDO_Firebird, pdo_mysql, PDO_ODBC, pdo_pgsql, pdo_sqlite, pdo_sqlsrv, pgsql, Phar, posix, readline, shmop, SimpleXML, snmp, soap, sockets, sqlite3, sqlsrv, sysvmsg, sysvsem, sysvshm, tidy, tokenizer, xmlreader, xmlwriter, xsl, yaml, zip, zmq, memcached, redis, ds, Zend OPcache |
| php_xdebug | ✗ |
| opcache_extension_loaded | ✓ |
| opcache_enabled | ✗ |

## Reports

- [OutputBenchmark](./OutputBenchmark.md)
  - [QREps](./OutputBenchmark.md#qreps)
  - [QRFpdf](./OutputBenchmark.md#qrfpdf)
  - [QRGdImageJPEG](./OutputBenchmark.md#qrgdimagejpeg)
  - [QRGdImagePNG](./OutputBenchmark.md#qrgdimagepng)
  - [QRGdImageWEBP](./OutputBenchmark.md#qrgdimagewebp)
  - [QRImagick](./OutputBenchmark.md#qrimagick)
  - [QRMarkupSVG](./OutputBenchmark.md#qrmarkupsvg)
  - [QRMarkupXML](./OutputBenchmark.md#qrmarkupxml)
  - [QRStringJSON](./OutputBenchmark.md#qrstringjson)
- [QRDataBenchmark](./QRDataBenchmark.md)
  - [invocation](./QRDataBenchmark.md#invocation)
  - [writeMatrix](./QRDataBenchmark.md#writematrix)
  - [decodeSegment](./QRDataBenchmark.md#decodesegment)
- [MaskPatternBenchmark](./MaskPatternBenchmark.md)
  - [getBestPattern](./MaskPatternBenchmark.md#getbestpattern)
- [DecoderBenchmark](./DecoderBenchmark.md)
  - [GDLuminanceSource](./DecoderBenchmark.md#gdluminancesource)
  - [IMagickLuminanceSource](./DecoderBenchmark.md#imagickluminancesource)
- [QRCodeBenchmark](./QRCodeBenchmark.md)
  - [render](./QRCodeBenchmark.md#render)