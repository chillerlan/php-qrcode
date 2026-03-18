# Benchmark results

## Environment

| Name | Value |
|------|-------|
| date | 2026-03-18 18:29:32 |
| environment | Linux #17~24.04.1-Ubuntu SMP Mon Dec  1 20:10:50 UTC 2025, x86_64 |
| tag | &lt;current&gt; |
| php_version | 8.5.4 |
| php_ini | /etc/php/8.5/cli/php.ini |
| php_extensions | Core, date, lexbor, openssl, pcre, zlib, filter, hash, json, uri, Zend OPcache, pcntl, random, Reflection, SPL, session, standard, sodium, libxml, mysqlnd, PDO, xml, apcu, ast, bcmath, bz2, calendar, ctype, curl, dba, dom, enchant, mbstring, FFI, fileinfo, ftp, gd, gettext, gmp, iconv, igbinary, imagick, imap, intl, ldap, exif, memcache, mongodb, msgpack, mysqli, odbc, pdo_dblib, PDO_Firebird, pdo_mysql, PDO_ODBC, pdo_pgsql, pdo_sqlite, pdo_sqlsrv, pgsql, Phar, posix, readline, shmop, SimpleXML, snmp, soap, sockets, sqlite3, sqlsrv, sysvmsg, sysvsem, sysvshm, tidy, tokenizer, xmlreader, xmlwriter, xsl, yaml, zip, zmq, memcached, redis, ds |
| php_xdebug | ✗ |
| opcache_extension_loaded | ✓ |
| opcache_enabled | ✗ |

## Reports

- [DecoderBenchmark](./DecoderBenchmark.md)
  - [GDLuminanceSource](./DecoderBenchmark.md#gdluminancesource)
  - [IMagickLuminanceSource](./DecoderBenchmark.md#imagickluminancesource)
- [MaskPatternBenchmark](./MaskPatternBenchmark.md)
  - [getBestPattern](./MaskPatternBenchmark.md#getbestpattern)
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
  - [QRStringText](./OutputBenchmark.md#qrstringtext)
- [QRDataBenchmark](./QRDataBenchmark.md)
  - [invocation](./QRDataBenchmark.md#invocation)
  - [writeMatrix](./QRDataBenchmark.md#writematrix)
  - [decodeSegment](./QRDataBenchmark.md#decodesegment)