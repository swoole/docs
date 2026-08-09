# String Method

## Mapping Table
Misalnya `$str = "hello world"`, gunakan `$str->length()` untuk mendapatkan panjang string, setara dengan memanggil `strlen($str)`.

| Method                    | Deskripsi                                    | Fungsi PHP yang Sesuai       |
|---------------------------|----------------------------------------------|------------------------------|
| length()                  | Mendapatkan panjang string                   | strlen()                     |
| isEmpty()                 | Mengecek apakah string kosong                | empty()                      |
| lower()                   | Mengubah string ke huruf kecil               | strtolower()                 |
| upper()                   | Mengubah string ke huruf besar               | strtoupper()                 |
| lowerFirst()              | Mengubah huruf pertama string ke kecil       | lcfirst()                    |
| upperFirst()              | Mengubah huruf pertama string ke besar       | ucfirst()                    |
| upperWords()              | Mengubah huruf pertama setiap kata ke besar  | ucwords()                    |
| addCSlashes()             | Menambahkan backslash sebelum karakter tertentu| addcslashes()               |
| addSlashes()              | Menambahkan backslash                        | addslashes()                 |
| chunkSplit()              | Memisahkan string menjadi potongan kecil      | chunk_split()                |
| countChars()              | Menghitung frekuensi setiap karakter          | count_chars()                |
| htmlEntityDecode()        | Mengubah HTML entity ke karakter             | html_entity_decode()         |
| htmlEntityEncode()        | Mengubah karakter ke HTML entity             | htmlentities()               |
| htmlSpecialCharsEncode()  | Mengubah karakter khusus ke HTML entity       | htmlspecialchars()           |
| htmlSpecialCharsDecode()  | Mengubah HTML entity ke karakter khusus       | htmlspecialchars_decode()    |
| trim()                    | Menghapus spasi di kedua ujung string         | trim()                       |
| lTrim()                   | Menghapus spasi di kiri string               | ltrim()                      |
| rTrim()                   | Menghapus spasi di kanan string              | rtrim()                      |
| parseStr()                | Mem-parsing query string menjadi variabel     | parse_str()                  |
| parseUrl()                | Mem-parsing URL dan mengembalikan komponennya | parse_url()                  |
| contains()                | Mengecek apakah string mengandung substring   | str_contains()               |
| incr()                    | Increment bagian numerik dalam string         | str_increment()              |
| decr()                    | Decrement bagian numerik dalam string         | str_decrement()              |
| pad()                     | Mengisi string ke panjang tertentu            | str_pad()                    |
| repeat()                  | Mengulang string sejumlah kali               | str_repeat()                 |
| replace()                 | Mengganti substring dalam string              | str_replace()                |
| iReplace()                | Mengganti substring (case-insensitive)        | str_ireplace()               |
| shuffle()                 | Mengacak urutan karakter string              | str_shuffle()                |
| split()                   | Memisahkan string menjadi array              | explode()                    |
| startsWith()              | Mengecek apakah string diawali substring     | str_starts_with()            |
| endsWith()                | Mengecek apakah string diakhiri substring    | str_ends_with()              |
| wordCount()               | Menghitung jumlah kata dalam string           | str_word_count()             |
| iCompare()                | Membandingkan string (case-insensitive)       | strcasecmp()                 |
| compare()                 | Membandingkan string (case-sensitive)         | strcmp()                     |
| find()                    | Mencari posisi pertama substring              | strstr()                     |
| iFind()                   | Mencari posisi pertama substring (case-insensitive)| stristr()               |
| stripTags()               | Menghapus tag HTML dan PHP                   | strip_tags()                 |
| stripCSlashes()           | Menghapus backslash                          | stripcslashes()              |
| stripSlashes()            | Menghapus backslash                          | stripslashes()               |
| iIndexOf()                | Mencari posisi pertama substring (case-insensitive)| stripos()               |
| indexOf()                 | Mencari posisi pertama substring             | strpos()                     |
| lastIndexOf()             | Mencari posisi terakhir substring            | strrpos()                    |
| iLastIndexOf()            | Mencari posisi terakhir substring (case-insensitive)| strripos()             |
| lastCharIndexOf()         | Mencari posisi terakhir karakter             | strrchr()                    |
| substr()                  | Mengembalikan substring string               | substr()                     |
| substrCompare()           | Membandingkan substring string               | substr_compare()             |
| substrCount()             | Menghitung jumlah kemunculan substring        | substr_count()               |
| substrReplace()           | Mengganti substring dengan string lain        | substr_replace()             |
| reverse()                 | Membalik string                              | strrev()                     |
| md5()                     | Menghitung hash MD5 string                   | md5()                        |
| sha1()                    | Menghitung hash SHA1 string                  | sha1()                       |
| crc32()                   | Menghitung nilai CRC32 string                | crc32()                      |
| hash()                    | Menghitung hash string                       | hash()                       |
| hashCode()                | Menghitung kode hash string                  | swoole_hashcode()            |
| base64Decode()            | Melakukan Base64 decode pada string           | base64_decode()              |
| base64Encode()            | Melakukan Base64 encode pada string           | base64_encode()              |
| urlDecode()               | Melakukan URL decode pada string             | urldecode()                  |
| urlEncode()               | Melakukan URL encode pada string             | urlencode()                  |
| rawUrlEncode()            | Melakukan raw URL encode pada string          | rawurlencode()               |
| rawUrlDecode()            | Melakukan raw URL decode pada string          | rawurldecode()               |
| match()                   | Mencocokkan string dengan regex              | preg_match()                 |
| matchAll()                | Mencocokkan semua substring dengan regex     | preg_match_all()             |
| isNumeric()               | Mengecek apakah string numerik               | is_numeric()                 |

## Multibyte String Method Mapping
| Method                    | Deskripsi                                    | Fungsi PHP yang Sesuai       |
|---------------------------|----------------------------------------------|------------------------------|
| mbUpperFirst()            | Mengubah huruf pertama multibyte ke besar     | mb_ucfirst()                 |
| mbLowerFirst()            | Mengubah huruf pertama multibyte ke kecil     | mb_lcfirst()                 |
| mbTrim()                  | Menghapus spasi di kedua ujung multibyte     | mb_trim()                    |
| mbSubstrCount()           | Menghitung jumlah substring multibyte         | mb_substr_count()            |
| mbSubstr()                | Mengembalikan substring multibyte            | mb_substr()                  |
| mbUpper()                 | Mengubah multibyte ke huruf besar            | mb_strtoupper()              |
| mbLower()                 | Mengubah multibyte ke huruf kecil            | mb_strtolower()              |
| mbFind()                  | Mencari posisi pertama substring multibyte   | mb_strstr()                  |
| mbIndexOf()               | Mencari posisi pertama substring multibyte   | mb_strpos()                  |
| mbLastIndexOf()           | Mencari posisi terakhir substring multibyte  | mb_strrpos()                 |
| mbILastIndexOf()          | Mencari posisi terakhir substring (case-insensitive)| mb_strripos()          |
| mbLastCharIndexOf()       | Mencari posisi terakhir karakter multibyte   | mb_strrchr()                 |
| mbILastCharIndex()        | Mencari posisi terakhir karakter (case-insensitive)| mb_strrichr()           |
| mbLength()                | Mendapatkan panjang multibyte string          | mb_strlen()                  |
| mbIFind()                 | Mencari posisi pertama substring (case-insensitive)| mb_stristr()           |
| mbIIndexOf()              | Mencari posisi pertama substring (case-insensitive)| mb_stripos()           |
| mbCut()                   | Mengembalikan substring multibyte (by byte)  | mb_strcut()                  |
| mbRTrim()                 | Menghapus spasi di kanan multibyte           | mb_rtrim()                   |
| mbLTrim()                 | Menghapus spasi di kiri multibyte            | mb_ltrim()                   |

## Serialization Method
| Method                  | Deskripsi                                     | Fungsi PHP yang Sesuai |
|-------------------------|-----------------------------------------------|------------------------|
| jsonDecode()            | Mendekode string JSON ke associative array     | json_decode()          |
| jsonDecodeToObject()    | Mendekode string JSON ke object               | json_decode()          |
| unmarshal()             | Alias dari unserialize                         | unserialize()          |
| unserialize()           | Mendeserialisasi variabel ke PHP variable      | unserialize()          |


## Perbedaan

- `replace` dan `iReplace` menyesuaikan urutan parameter dibanding fungsi PHP, string yang dioperasikan dijadikan parameter pertama
- `jsonDecode` selalu mengembalikan associative array, bukan object. `jsonDecodeToObject()` selalu mengembalikan object `stdClass`
- Method `split` setara dengan fungsi `explode`, menyesuaikan urutan parameter dengan string yang dioperasikan sebagai parameter pertama
- Method `match()` dan `matchAll()` langsung mengembalikan array hasil pencocokan, bukan jumlah kecocokan. Gunakan `count($result[1])` untuk mendapatkan jumlah

```php
$str = 'foobarbaz';
$regex1 = '/(foo)(bar)(baz)/';
$matches = $str->match($regex1, PREG_OFFSET_CAPTURE);

preg_match($regex1, $str, $matches2, PREG_OFFSET_CAPTURE);
Assert::eq($matches, $matches2);
```
