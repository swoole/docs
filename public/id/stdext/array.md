# Array Method

## Mapping Table
Misalnya `$array = [1, 2, 3]`, gunakan `$array->count()` untuk mendapatkan panjang array, setara dengan memanggil `count($array)`.

| Method              | Deskripsi                                          | Fungsi PHP yang Sesuai          |
|---------------------|----------------------------------------------------|---------------------------------| 
| all()               | Mengecek apakah semua elemen memenuhi kondisi callback | array_all()                  |
| any()               | Mengecek apakah ada elemen yang memenuhi kondisi callback | array_any()              |
| changeKeyCase()     | Mengubah key array ke huruf besar/kecil tertentu    | array_change_key_case()         |
| chunk()             | Memisahkan array menjadi beberapa bagian kecil      | array_chunk()                   |
| column()            | Mengembalikan nilai kolom tertentu dari array       | array_column()                  |
| countValues()       | Menghitung frekuensi setiap nilai dalam array        | array_count_values()            |
| diff()              | Menghitung selisih array                            | array_diff()                    |
| diffAssoc()         | Menghitung selisih array asosiatif                  | array_diff_assoc()              |
| diffKey()           | Menghitung selisih key array                        | array_diff_key()                |
| filter()            | Menyaring elemen array menggunakan callback         | array_filter()                  |
| find()              | Mencari elemen pertama yang memenuhi kondisi callback | array_find()                  |
| flip()              | Menukar key dan value array                         | array_flip()                    |
| intersect()         | Menghitung irisan array                             | array_intersect()               |
| intersectAssoc()    | Menghitung irisan array asosiatif                   | array_intersect_assoc()         |
| isList()            | Mengecek apakah array adalah list                   | array_is_list()                 |
| keyExists()         | Mengecek apakah key tertentu ada dalam array         | array_key_exists()              |
| keyFirst()          | Mendapatkan key pertama array                        | array_key_first()               |
| keyLast()           | Mendapatkan key terakhir array                       | array_key_last()                |
| keys()              | Mengembalikan semua key array                        | array_keys()                    |
| map()               | Memproses setiap elemen array dengan callback        | array_map()                     |
| pad()               | Mengisi array ke panjang tertentu dengan nilai tertentu | array_pad()                  |
| product()           | Menghitung produk semua nilai dalam array            | array_product()                 |
| rand()              | Mengambil satu atau lebih elemen secara acak         | array_rand()                    |
| reduce()            | Mereduksi array menjadi nilai tunggal dengan callback | array_reduce()                 |
| replace()           | Mengganti elemen array dengan satu atau lebih array   | array_replace()                 |
| reverse()           | Membalik urutan elemen array                         | array_reverse()                 |
| search()            | Mencari nilai dalam array dan mengembalikan key-nya  | array_search()                  |
| slice()             | Mengambil sebagian array                             | array_slice()                   |
| sum()               | Menghitung jumlah semua nilai dalam array            | array_sum()                     |
| unique()            | Menghapus nilai duplikat dalam array                 | array_unique()                  |
| values()            | Mengembalikan semua nilai array                      | array_values()                  |
| count()             | Mendapatkan jumlah elemen array                      | count()                         |
| merge()             | Menggabungkan satu atau lebih array                  | array_merge()                   |
| contains()          | Mengecek apakah array mengandung nilai tertentu       | in_array()                      |
| join()              | Menggabungkan elemen array menjadi string             | implode()                       |
| isEmpty()           | Mengecek apakah array kosong                         | empty()                         |

## Array Write Method
| Method           | Deskripsi                                          | Fungsi PHP yang Sesuai |
|------------------|----------------------------------------------------|------------------------| 
| sort()           | Mengurutkan array                                  | sort()                 |
| pop()            | Mengeluarkan elemen terakhir array                  | array_pop()            |
| push()           | Menambahkan satu atau lebih elemen ke akhir array    | array_push()           |
| shift()          | Mengeluarkan elemen pertama array                   | array_shift()          |
| unshift()        | Menambahkan satu atau lebih elemen ke awal array     | array_unshift()        |
| splice()         | Menghapus elemen di posisi tertentu dan bisa menyisipkan baru | array_splice() |
| walk()           | Memproses setiap elemen array dengan callback        | array_walk()           |
| replaceStr()     | Mengganti substring dalam elemen string array        | str_replace()          |
| iReplaceStr()    | Mengganti substring (case-insensitive) dalam elemen string array | str_ireplace() |

Perhatikan karena fungsi write array `PHP` parameter pertamanya adalah reference, tidak bisa langsung menggunakan method di atas pada array. Perlu diubah ke reference variable terlebih dahulu.

```php
$array = [ 'apple', 'banana', 'cherry' ];
$ref = &$array;
$ref->push('orange');
var_dump($ref, $array); // Keduanya: [ 'apple', 'banana', 'cherry', 'orange' ]
```

## Array Serialization Method
| Method          | Deskripsi                                     | Fungsi PHP yang Sesuai |
|-----------------|-----------------------------------------------|------------------------| 
| serialize()     | Menyerialiasi array ke string                  | serialize()            |
| marshal()       | Menyerialiasi array ke string (alias)          | serialize()            |
| jsonEncode()    | Meng-encode array ke string `JSON`             | json_encode()          |


## Perbedaan
- `keyExists`, `map`, `replaceStr` dan `iReplaceStr` menyesuaikan urutan parameter fungsi PHP, menjadikan array yang dioperasikan sebagai parameter pertama
- Nama method `join` menggantikan fungsi PHP `implode`
- Nama method `contains` menggantikan fungsi PHP `in_array`
