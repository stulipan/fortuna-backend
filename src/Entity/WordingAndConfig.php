<?php

namespace App\Entity;

class WordingAndConfig
{
    public const IS_SINGLE = true;
//    public const IS_SINGLE = false;

//----------------------------------
        // ÁLTALÁNOS
//    public const PREFIX = "Meghoztam a mai Horoszkópodat, kedves {{cuf_9232715|fallback:\"barátom\"}}!";      // LUNA
//    public const PREFIX = "Megérkezett a mai horoszkópod, kedves {{cuf_9232715|fallback:\"barátom\"}}!";      // LUNA
//    public const PREFIX = "Remélem jól indul a napod, kedves {{cuf_9232715|fallback:\"barátom\"}}!";         // LUNA
//    public const PREFIX = "Megérkezett a mai horoszkópod, kedves {{cuf_9232715|fallback:\"barátom\"}}!";      // LUNA
//    public const PREFIX = "Itt a mai horoszkópod, kedves {{cuf_9232715|fallback:\"barátom\"}}!";      // LUNA
//    public const PREFIX = "Szép reggelt, kedves {{cuf_9232715|fallback:\"barátom\"}}!";                       // LUNA
//    public const PREFIX = "Üdvözöllek, kedves {{cuf_9232715|fallback:\"barátom\"}}!";                         // LUNA

        // HÉTFŐ
//    public const PREFIX = "Remélem pihentető hétvégéd volt, kedves {{cuf_9232715|fallback:\"barátom\"}}!";   // LUNA
        // HÉTVÉGE
//    public const PREFIX = "Remélem jól indul a hétvégéd, kedves {{cuf_9232715|fallback:\"barátom\"}}!";     // LUNA
//    public const PREFIX = "Remélem jól telik a hétvégéd, kedves {{cuf_9232715|fallback:\"barátom\"}}!";     // LUNA
//    public const PREFIX = "Bízom benne, hogy jól telik a hétvégéd, kedves {{cuf_9232715|fallback:\"barátom\"}}!";     // LUNA


        // ÁLTALÁNOS
    public const PREFIX = "Üdvözöllek, kedves {{cuf_9284767|fallback:\"barátom\"}}!";                             //   csak FORTUNA !!!
//    public const PREFIX = "Szia, kedves {{cuf_9284767|fallback:\"barátom\"}}!";                                   //   csak FORTUNA !!!
//    public const PREFIX = "Szia, kedves {{cuf_9284767|fallback:\"barátom\"}}! Meghoztam a mai üzeneted.";         //   csak FORTUNA !!!
//    public const PREFIX = "Hogy vagy, kedves {{cuf_9284767|fallback:\"barátom\"}}? Meghoztam a mai horoszkópod."; //   csak FORTUNA !!!
//    public const PREFIX = "Szép reggelt, kedves {{cuf_9284767|fallback:\"barátom\"}}!";                           //   csak FORTUNA !!!
//    public const PREFIX = "Szép jó reggelt, {{cuf_9284767|fallback:\"barátom\"}}, itt is van a mai horoszkópod!"; //   csak FORTUNA !!!
//    public const PREFIX = "Jó reggelt, kedves {{cuf_9284767|fallback:\"barátom\"}}!";                             //   csak FORTUNA !!!
//    public const PREFIX = "Vidám reggelt, kedves {{cuf_9284767|fallback:\"barátom\"}}!";                          //   csak FORTUNA !!!
//    public const PREFIX = "Remélem jól vagy, kedves {{cuf_9284767|fallback:\"barátom\"}}!";                       //   csak FORTUNA !!!
//    public const PREFIX = "Meghoztam a mai üzeneted, kedves {{cuf_9284767|fallback:\"barátom\"}}!";               //   csak FORTUNA !!!
//    public const PREFIX = "Itt a legújabb horoszkópod, kedves {{cuf_9284767|fallback:\"barátom\"}}!";             //   csak Fortuna !!!
//    public const PREFIX = "Meghoztam a mai Horoszkópodat, kedves {{cuf_9284767|fallback:\"barátom\"}}!";          //   csak Fortuna !!!
//    public const PREFIX = "Megérkezett a mai horoszkópod, kedves {{cuf_9284767|fallback:\"barátom\"}}!";          //   csak FORTUNA !!!
//        // HÉTVÉGE
//    public const PREFIX = "Szép szombati reggelt, kedves {{cuf_9284767|fallback:\"barátom\"}}!";                  //   csak FORTUNA !!!
//    public const PREFIX = "Remélem jól indul a hétvégéd, kedves {{cuf_9284767|fallback:\"barátom\"}}!";           //   csak FORTUNA !!!
//    public const PREFIX = "Szép vasárnap reggelt, kedves {{cuf_9284767|fallback:\"barátom\"}}!";                  //   csak FORTUNA !!!
//    public const PREFIX = "Meg is hoztam a vasárnapi Horoszkópod, {{cuf_9284767|fallback:\"barátom\"}}!";         //   csak FORTUNA !!!
//        // HÉTFŐ
//    public const PREFIX = "Remélem pihentető hétvégéd volt, kedves {{cuf_9284767|fallback:\"barátom\"}}!";        //   csak FORTUNA !!!
//    public const PREFIX = "Hogy telt a hétvégéd, kedves {{cuf_9284767|fallback:\"barátom\"}}? Remélem jól!";      //   csak FORTUNA !!!


//    public const PREFIX = "Salutare, {{cuf_9323449|fallback:\"prietene\"}}!";                                     // LUNA RO
//    public const PREFIX = "Bun găsit, {{cuf_9323449|fallback:\"prietene\"}}!";                                    // LUNA RO
//    public const PREFIX = "Bine te-am găsit, {{cuf_9323449|fallback:\"prietene\"}}!";                             // LUNA RO
//    public const PREFIX = "Bună dimineața, {{cuf_9323449|fallback:\"prietene\"}}!";                               // LUNA RO
//    public const PREFIX = "Ți-am adus horoscopul de azi, {{cuf_9323449|fallback:\"prietene\"}}!";                 // LUNA RO
//    public const PREFIX = "Dragă {{cuf_9323449|fallback:\"prietene\"}}, iată mesajul tău de azi!";                // LUNA RO
//    public const PREFIX = "Dragă {{cuf_9323449|fallback:\"prietene\"}}, iată horoscopul tău de azi!";             // LUNA RO
//    public const PREFIX = "{{cuf_9323449|fallback:\"Prietene\"}}, a sosit horoscopul tău de azi!";                // LUNA RO
//    public const PREFIX = "{{cuf_9323449|fallback:\"Prietene\"}}, ți-am adus horoscopul de azi!";                 // LUNA RO
//    public const PREFIX = "Îți doresc un început de weekend plăcut, {{cuf_9323449|fallback:\"prietene\"}}!";      // LUNA RO
//    public const PREFIX = "Să ai un început de weekend superb, {{cuf_9323449|fallback:\"prietene\"}}!";           // LUNA RO
//    public const PREFIX = "Sper că ai avut un weekend odihnitor, {{cuf_9323449|fallback:\"prietene\"}}!";         // LUNA RO
//    public const PREFIX = "Sper că ești bine, dragă {{cuf_9323449|fallback:\"prietene\"}}!";                      // LUNA RO


//----------------------------------
//    public const POSTFIX = "(A folytatáshoz nyomj a Tovább gombra!)";                                                                         /// EZT CSAK AKKOR HA 2 reszes HORIT KULDUNK !!!!!
//    public const POSTFIX = "(Kérlek nyomj a gombra vagy írj ide valamit, mert csak így tudom biztosan elküldeni a holnapi horoszkópot!)";     // LUNA

//    public const POSTFIX = "(Ahhoz, hogy el tudjam biztosan küldeni a holnapi horoszkópot, kérlek nyomj a gombra vagy írj ide valamit.)";      // csak FORTUNA
//    public const POSTFIX = "(Kérlek írj ide valamit vagy nyomj a gombra, hogy holnap is biztosan el tudjam küldeni a horoszkópod!)";          // csak FORTUNA
    public const POSTFIX = "(Kérlek nyomj a gombra vagy írj ide valamit, hogy holnap is biztosan el tudjam küldeni a horoszkópod!)";          // csak FORTUNA

//    public const POSTFIX = "(Pentru a continua, apasă pe butonul \"Mai departe\".)";                                                          // LUNA RO
//    public const POSTFIX = "(Apasă pe buton sau scrie ceva, căci numai așa îți pot trimite cu siguranță horoscopul de mâine!)";               // LUNA RO
//    public const POSTFIX = "(Apasă pe buton căci numai așa îți pot trimite horoscopul de mâine!)";                                            // LUNA RO
}
