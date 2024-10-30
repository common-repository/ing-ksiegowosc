=== ING Księgowość ===
Contributors: radoslawlyzniak
Tags: ing, ingksięgowość, księgowość, faktury, invoices
Requires at least: 4.7
Tested up to: 6.4.3
Requires PHP: 5.6.0
License: GPLv2
Stable tag: 1.0.5

Niech faktury za zakupy Twoich klientów wystawiają się automatycznie! Wtyczka pozwala na powiązanie sklepu z kontem firmy w aplikacji ING Księgowość

== Description ==
ING Księgowość to aplikacja pozwalająca na rejestrowanie faktur zakupu i sprzedaży oraz ich zaksięgowanie – dzięki temu masz wszystkie sprawy firmy w jednym miejscu. Jeśli dodatkowo posiadasz rachunek firmowy w ING, możesz zlecać płatności za dokumenty kosztowe.

[Załóż rachunek dla firmy w ING Banku](https://www.ing.pl/lp/konto-dla-firmy-otworz?site=1&utm_source=udb&utm_medium=ksiegowosc&utm_campaign=WordPress_appstore)

Nasza wtyczka działa w powiązaniu z wtyczką WooCommerce.
Po dokonaniu zakupu w Twoim sklepie informacja na temat zrealizowanej płatności trafia do ING Księgowość, gdzie automatycznie jest tworzona faktura dla klienta.

Jeżeli prowadzisz sprzedaż także dla firm, zalecamy dodatkowo zainstalowanie wtyczki Flexible Chechout Fields for WooCommerce – pozwala ona na dodanie do formularza płatności pola na numer NIP.

[Sprawdź, jak powiązać WooCommerce z ING Księgowość krok po kroku](https://www.ingksiegowosc.pl/_fileserver/item/qz7baiy)

== Installation ==

Uwaga – przed instalacją wtyczki upewnij się, że posiadasz już zainstalowaną wtyczkę WooCommerce, skonfigurowany sklep oraz produkty.

* Po pobraniu wtyczki ING Księgowość zaloguj się do panelu firmy w ING Księgowość [https://www.ingksiegowosc.pl](https://www.ingksiegowosc.pl)
* Przejdź w nim do zakładki Dane i ustawienia > Integracje > Klucz API. Kliknij 'Generuj' i skopiuj do schowka cały klucz
* Wróć do wtyczki. Przejdź do jej Ustawień i wklej klucz w polu 'Klucz API'
* Zainstaluj wtyczkę Flexible Checkout Fields. Przejdź do zakładki 'Wtyczki' i wybierz 'Ustawienia'. Dodaj do swojego formularza w dowolnym miejscu pole Tekst lub Liczba. Nadaj mu nazwę NIP. Skopiuj wartość widoczną w polu 'Nazwa meta'.
* Przejdź do ustawień wtyczki ING Księgowość. Wklej skopiowaną wartość w polu 'Meta dla NIP'

Gotowe! Teraz Twoje faktury będą wystawiać się automatycznie w ING Księgowość.

== Frequently Asked Questions ==

=Czy muszę mieć konto firmowe w ING, żeby korzystać z ING Księgowość?=
Nie, nie jest to wymagane. Dowiedz się więcej na [ingksiegowosc.pl](https://www.ingksiegowosc.pl/).

=Czy korzystanie z wtyczki jest dodatkowo płatne?=
Nie, korzystanie z naszej wtyczki nie pociąga za sobą dodatkowych kosztów.

=Czy mogę powiązać moje konto w ING Księgowość z kontem dowolnego sklepu?=
W tej chwili jest to możliwe wyłącznie dla sklepów obsługiwanych w WooCommerce.

=Jestem wzrokowcem – czy macie jakąś instrukcję?=
Tak, możesz podejrzeć ją [tutaj](https://www.ingksiegowosc.pl/_fileserver/item/qz7baiy)

== Screenshots ==

1. Ustawienia wtyczki
2. Listę faktur utworzonych w ING Księgowość zgodnie z zamówieniami WooCommerce.
3. Pulpit nawigacyjny firmy w ING Księgowość.

== Changelog ==

= 1.0.5 =
* added support for tax exemption

= 1.0.4 =
* fix for format string

= 1.0.3 =
* rename plugin

= 1.0.2 =
* adjustment plugin for requirements

= 1.0.1 =
* fix for constant name

= 1.0.0 =
* plugin release
