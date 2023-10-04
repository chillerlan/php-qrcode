# Popular content and URI schemes

**A brief summary of popular use cases for QR codes.**


## URL `https`

Perhaps the most common use for QR Codes is to share URLs: simply encode the (URL-encoded) string value
and make sure to include the scheme `http(s)://` so that it can be properly identified by the reader application:

```
https://en.m.wikipedia.org/wiki/URL
```

Some URLs may open a vendor specific application, for example the following URL may open the YouTube app:

```
https://www.youtube.com/watch?v=dQw4w9WgXcQ
```

**See also:**

- [What is a URL? (Mozilla MDN)](https://developer.mozilla.org/en-US/docs/Learn/Common_questions/Web_mechanics/What_is_a_URL)
- [URL (Wikipedia)](https://en.wikipedia.org/wiki/URL)


## E-Mail `mailto`

E-Mail links are encoded similar to URLs, preceded by the `mailto:` scheme to ensure they are properly identified and the default mail application can be opened.
Parameters may be added as a [URL query string](https://en.wikipedia.org/wiki/Query_string):
```
mailto:<ADDR>[,...?to=<ADDR>,...&cc=<ADDR>,...&bcc=<ADDR>,...&subject=<SUBJECT>&body=<BODY>]
```

| Query parameter | Description                                                                                                                                  |
|-----------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| `to`            | Alternative to add a recipient address: `mailto:<ADDR>?to=<ADDR>` is equivalent to<br/>`mailto:<ADDR>,<ADDR>` and `mailto:?to=<ADDR>,<ADDR>` |
| `subject`       | Subject text: `mailto:<ADDR>?subject=Hello%20World%21` would open the e-mail app<br/>and create a messagewith the subject `Hello World!`     |
| `body`          | Message body: `mailto:<ADDR>?body=This%20is%20the%20message%20body.`<br/>creates a message with the contents `This is the message body.`     |
| `cc`            | "Carbon copy" to add one or more `cc` recipients                                                                                             |
| `bcc`           | "Blind carbon copy" to add one or more `bcc` recipients                                                                                      |

The fields for recipient adresses (`mailto:`, `to`, `cc`, `bcc`) may contain one or more elements separated by a comma `,`; the values of all fields shall be URL-encoded according to [RFC 3986](https://datatracker.ietf.org/doc/html/rfc3986).

**Examples:**

- `mailto:?to=addr1@example.com`
- `mailto:addr1@example.com,addr2@example.com`
- `mailto:addr1@example.com?cc=addr2@example.com&subject=Hello%21`

**See also:**

  - [RFC 6068 - The 'mailto' URI Scheme](https://datatracker.ietf.org/doc/html/rfc6068)
  - [Email links (Mozilla MDN)](https://developer.mozilla.org/en-US/docs/Learn/HTML/Introduction_to_HTML/Creating_hyperlinks#email_links)
  - [Email (Wikipedia)](https://en.wikipedia.org/wiki/Email)
  - [mailto (Wikipedia)](https://en.wikipedia.org/wiki/Mailto)


## Phone numbers `tel`

A phone number should be prefixed with the `tel:` scheme so that a device's dialer can be invoked properly:

```
tel:+999-123-456-7890
```

Generally, the most complete version of a telephone number possible sould be used, e.g. `+<country code><area code><number>`, spaces or hyphens may be used to separate blocks.
Some devices may also support the `sms` and `fax` schemes, which are deprecated in favor of `tel`.

**See also:**

  - [RFC 3966 - The 'tel' URI for Telephone Numbers](https://datatracker.ietf.org/doc/html/rfc3966)
  - [List of country calling codes (Wikipedia)](https://en.wikipedia.org/wiki/List_of_country_calling_codes)
  - [National conventions for writing telephone numbers (Wikipedia)](https://en.wikipedia.org/wiki/National_conventions_for_writing_telephone_numbers)
  - [google/libphonenumber (GitHub)](https://github.com/google/libphonenumber) ([PHP port (GitHub)](https://github.com/giggsey/libphonenumber-for-php))


## Geo Coordinates `geo`

A geographical coordinate, including altitude can be encoded using the `geo:` URI scheme so that it can be opened with a map application.

```
geo:<latitude>,<longitude>[,<altitude>;crs=<crs>;u=<num>]
```

The default coordinate system is WGS-84, for which latitude and longitude should be supplied as decimal degrees, the optional altitude in meters.
The parameter `u` can be used to specify an *uncertainty* value (in meters), a different *coordinate reference system* may be supplied with `crs`.

Some applications support an additional query string with values of `z` for *zoom* level and `q` for a local search *query* (URL-encoded):
```
geo:<latitude>,<longitude>?z=<zoom>&q=<search>
```

**Examples:**

- `geo:47.620521,-122.349293`
- `geo:27.988056,86.925278,8848`
- `geo:11.373333,142.591667,-10920;u=10`
- `geo:37.786971,-122.399677;crs=Moon-2011;u=35`

**See also:**

- [RFC 5870 - A Uniform Resource Identifier for Geographic Locations ('geo' URI)](https://datatracker.ietf.org/doc/html/rfc5870)
- [geo URI scheme (Wikipedia)](https://en.wikipedia.org/wiki/Geo_URI_scheme)
- [Coordinate reference system (Wikipedia)](https://en.wikipedia.org/wiki/Coordinate_reference_system)


## Mobile Authenticators `otpauth`

Mobile authenticators can be added with the `otpauth` scheme:

```
otpauth://<MODE>/<LABEL>?secret=<SECRET>[&issuer=<ISSUER>&params...]
```

The path elemets `MODE` and `LABEL` as well as the query parameter `secret` are mandatory, other query parameters are optional,
however, it is strongy advised to add the `issuer` parameter to ease identification.
The `LABEL`, as well as the `issuer` values shall be URL-encoded according to [RFC 3986](https://datatracker.ietf.org/doc/html/rfc3986).

| Path element | Description                                                                                                                                                   |
|--------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `MODE`       | Authenticator mode, either `totp` (time based) or `hotp` (counter based)                                                                                      |
| `LABEL`      | The label is used to identify which account a key is associated with.<br/>It may be prefixed with the issuer name, separated by a colon: `<issuer>:<account>` |


| Query parameter | Description                                                                                                                                                                                                                                          |
|-----------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `secret`        | Secret key (required), a cryptographically random string, encoded in Base32<br/>according to [RFC 3548](https://datatracker.ietf.org/doc/html/rfc3548) (without padding).<br/>Some authenticators may support Base64 and hexadecimal values as well. |
| `issuer`        | A string value indicating the provider or service this account is associated with.                                                                                                                                                                   |
| `algorithm`     | Hash algorithm, may be one of `SHA1` (default), `SHA256` or `SHA512`                                                                                                                                                                                 |
| `digits`        | Length of the OTP code: `6` or `8`                                                                                                                                                                                                                   |
| `counter`       | (`hotp` only, required) The initial counter value                                                                                                                                                                                                    |
| `period`        | (`totp` only) The period of time in seconds a code will be valid for (default: 30)                                                                                                                                                                   |

The parameters `algorithm`, `digits` and `period` may not be supported by some devices/apps.

**Examples:**

- `otpauth://hotp/example.com:counter-based?secret=JBSWY3DPEHPK3PXP&counter=42`
- `otpauth://hotp/counter-based?secret=JBSWY3DPEHPK3PXP&issuer=example.com&digits=6&algorithm=SHA256&counter=42`
- `otpauth://totp/example.com%3Atime-based?secret=JBSWY3DPEHPK3PXP`
- `otpauth://totp/time-based?secret=JBSWY3DPEHPK3PXP&issuer=example.com&digits=8&algorithm=SHA512&period=60`

**See also:**

- [RFC 4226 - An HMAC-Based One-Time Password Algorithm](https://datatracker.ietf.org/doc/html/rfc4226)
- [RFC 6238 - Time-Based One-Time Password Algorithm](https://datatracker.ietf.org/doc/html/rfc6238)
- [Google Authenticator (Wikipedia)](https://en.wikipedia.org/wiki/Google_Authenticator)
- [google-authenticator: Key Uri Format (GitHub)](https://github.com/google/google-authenticator/wiki/Key-Uri-Format)
- [php-qrcode: Authenticator example (GitHub)](https://github.com/chillerlan/php-qrcode/blob/main/examples/authenticator.php)


## Wireless Network configuration

Wi-Fi configuration general syntax looks as follows:

```
WIFI:S:<SSID>[;T:<TYPE>;P:<PASSWORD>;H:<HIDDEN>;];
```

| Parameter       | Description                                                                                                                            |
|-----------------|----------------------------------------------------------------------------------------------------------------------------------------|
| `S`<sup>*</sup> | Network SSID (required)                                                                                                                |
| `T`             | Authentication type: can be one of `WEP`, `WPA`, `WPA2-EAP`<br/>or `nopass` for no password (in which case you can omit the parameter) |
| `P`<sup>*</sup> | Password, ignored if parameter `T` is set to `nopass`                                                                                  |
| `H`             | Set to `true` the network SSID is hidden.                                                                                              |

<sup>*</sup> the value shall be enclosed in double quotes `"` if it is an ASCII string that can be interpreted as hex, e.g. `"ABCD1234"`,
special characters `\ ; , " :` shall be escaped with a backslash `\`.

Additional parameters for WPA2 and WPA3 (please note that these parameters may not be supported by some devices):

| Parameter | Description                                                                                                                      |
|-----------|----------------------------------------------------------------------------------------------------------------------------------|
| `A`       | WPA2-EAP: Anonymous identity                                                                                                     |
| `E`       | WPA2-EAP: EAP method, like `TTLS` or `PWD`                                                                                       |
| `PH2`     | WPA2-EAP: Phase 2 method, like `MSCHAPV2`                                                                                        |
| `I`       | WPA2-EAP, WPA3: UTF-8 encoded password identifier, present if<br/>the password has an SAE password identifier                    |
| `K`       | WPA3: DER of ASN.1 SubjectPublicKeyInfo in compressed form<br/>and encoded in “base64”, present when the network supports SAE-PK |
| `R`       | WPA3: Transition Disable value                                                                                                   |

**Examples:**

- `WIFI:S:MyNetworkWihoutPassword;;`
- `WIFI:S:MyNetworkWihoutPassword;T:nopass;P:;;`
- `WIFI:S:MyHiddenWpaNetwork;T:WPA;P:"PASSWORD123";H:true;;`
- `WIFI:S:MyHiddenWpa2Network;T:WPA2-EAP;P:"PASSWORD123";H:true;;`

**See also:**

- [WPA3 Specification, Section 7.1](https://www.wi-fi.org/download.php?file=/sites/default/files/private/WPA3%20Specification%20v3.1.pdf)
- [Wi-Fi Protected Access (Wikipedia)](https://en.wikipedia.org/wiki/Wi-Fi_Protected_Access)


## Contact information: vCard

The vCard is the most commonly used format to exchange contact details. It's too complex to fully explain here, instead just a bare minimum example:

```
BEGIN:VCARD
VERSION:4.0
N:<NAME>
FN:<FULL NAME>
GENDER:O
EMAIL;type=(WORK|HOME):<EMAIL>
TEL;type=(WORK|CELL|HOME):<PHONE>
ADR;type=WORK:<LINE1>;<LINE2>;<STREET>;<CITY>;<STATE>;<ZIP>;<COUNTRY>
TZ:<CITY/COUNTRY>
URL:<URL>
PHOTO;JPEG:<LINK>
LOGO;JPEG:<LINK>
NOTE:<TEXT>
CATEGORIES:<LIST>
END:VCARD
```

**See also:**

- [RFC 6350 - vCard Format Specification](https://datatracker.ietf.org/doc/html/rfc6350)
- [vCard (Wikipedia)](https://en.wikipedia.org/wiki/VCard)
- [vCard Ontology - for describing People and Organizations (W3C)](https://www.w3.org/TR/vcard-rdf/)
- [vobject library for PHP (GitHub)](https://github.com/sabre-io/vobject)


## Calendar Events: vCalendar and iCalendar

Calendar events can be shared via the iCalendar (formerly vCalendar) object (example from [icalendar-generator](https://github.com/spatie/icalendar-generator)):

```
BEGIN:VCALENDAR
VERSION:2.0
PRODID:spatie/icalendar-generator
NAME:Laracon online
X-WR-CALNAME:Laracon online
BEGIN:VEVENT
UID:5ef5c3f64cb2c
DTSTAMP;TZID=UTC:20200626T094630
SUMMARY:Creating calendar feeds
DTSTART:20190306T150000Z
DTEND:20190306T160000Z
DTSTAMP:20190419T135034Z
END:VEVENT
END:VCALENDAR
```

**See also:**

- [RFC 5545 - Internet Calendaring and Scheduling Core Object Specification (iCalendar)](https://datatracker.ietf.org/doc/html/rfc5545)
- [iCalendar (Wikipedia)](https://en.wikipedia.org/wiki/ICalendar)
- [vobject library for PHP (GitHub)](https://github.com/sabre-io/vobject)
- [iCalendar generator for PHP (GitHub)](https://github.com/spatie/icalendar-generator)


## See also

- [RFC 3986 - Uniform Resource Identifier (URI): Generic Syntax](https://datatracker.ietf.org/doc/html/rfc3986)
- [Uniform Resource Identifier (Wikipedia)](https://en.m.wikipedia.org/wiki/Uniform_Resource_Identifier)
- [List of URI schemes (Wikipedia)](https://en.m.wikipedia.org/wiki/List_of_URI_schemes)
- [zxing: Barcode Contents (GitHub)](https://github.com/zxing/zxing/wiki/Barcode-Contents)
