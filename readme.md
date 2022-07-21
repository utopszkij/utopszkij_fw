# Utopszkij_fw

PHP-MYSQL-VUE keretrendszer web oldalak fejlesztéséhez.

![logo](./images/utopszkij_fw.png)

## Tulajdonságok

- PHP, MYSQL backend, vue frontend,
- bootstrap, fontawesome,
- MVC struktúra,
- több nyelvüség támogatása,
- login/logout/regist rendszer, Google és Facebook login támogatása,
- usergroups rendszer,
- SEO url támogatása,
- facebook megosztás támogatása,
- egszerű telepíthetőség, nem szükséges konzol hozzáférés,
- verzió követés a github -ról.

## Dokumentáció

[]()

### A programot mindenki csak saját felelősségére használhatja.
						
## Lecensz

GNU v3

## A keretrendszer segitségével felépített müködő demo:

[https://szakacskonyv.nfx.hu](https://szakacskonyv.nfx.hu)

## Információk informatikusok számára      

## Szükséges sw környezet
### futtatáshoz
- web szerver   .htacces és rewrite támogatással
- php 7+ (mysqli kiegészítéssel)
- mysql 5+
### fejlesztéshez
- phpunit (unit test futtatáshoz)
- doxygen (php dokumentáció előállításhoz)
- nodejs (js unittesthez)
- php és js szintaxist támogató forrás szerkesztő vagy IDE

## Telepítés

- adatbázis létrehozása (utf8, magyar rendezéssel),
- config.php elkészítése a a config-example.php alapján,
- a views/impressum, policy, fájlok szükség szerinti módosítása
- fájlok és könyvtárak feltöltése a szerverre,
- az images könyvtár legyen irható a web szerver számára, a többi csak olvasható legyen,
- adatbázis kezdeti feltöltése a vendor/database/dbinit.sql segitségével,
- többfelhasználós üzemmód esetén; a program "Regisztrálás" menüpontjában hozzuk létre a
  a system adminisztrátor fiokot (a config.php -ban beállított bejelentkezési névvel).

Könyvtár szerkezet a futtató web szerveren:
```
[document_root]
  [images]
     kép fájlok (alkönyvtárak is lehetnek)
  [includes]
    [controllers]
      kontrollerek php fájlok
    [models]
      adat modellek php fájlok
    [views]
      viewer templates  spec. html fájlok. vue elemeket tartalmaznak
  [vendor]
    keretrendszer fájlok és harmadik féltől származó fájlok (több alkönyvtárat is tartalmaz)
  index.php  - fő program
  config.php - konfigurációs adatok
  style.css  - megjelenés
  files.txt  - a telepített fájlok felsorolása, az upgrade folyamat használja

```  
index.php paraméter nélküli hívással a "home.show" taskal indul a program.

index.php?task=upgrade1&version=vx.x&branch=xxxx hívással a github megadott branch -et használva  
is tesztelhető/használható az upgrade folyamat.

## unit test

Telepiteni kell a phpunit és a nodejs rendszert.

[https://phpunit.de/](https://phpunit.de/)

[https://nodejs.org/en/](https://nodejs.org/en/)

Létre kell hozni egy test adatbázist, az éles adatbázissal azonos strukturával.

Létre kell hozni egy tests/config_test.php fájlt az éles config.php alapján, a test adatbázishoz beállítva.

Ezután linux terminálban:
```
cd docroot
phpunit tests
./tools/viewtest.sh
```
## software documentáció

includes/views/swdoc.html

doc/html/index.html

## A sw. dokumentáció előállítása
telepiteni kell a doxygen dokumentáció krátort.

[https://doxygen.nl/](doxygen)  Köszönet a sw. fejlesztőinek.

A telepitési könyvtáraknak megfelelően módosítani kell documentor.sh fájlt.

Ezután linux terminálban:

```
cd docroot
./tools/documentor.sh
```
## verzió v1.0
2022.??.??
### *************************************





