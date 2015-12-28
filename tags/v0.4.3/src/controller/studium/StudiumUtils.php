<?php

namespace fajr\controller\studium;

fields::autoload();

class StudiumUtils {
  /**
   * ked odhlasujeme z predmetu, narozdiel od AISu robime opat
   * inicializaciu vsetkych aplikacii. Just for sure chceme
   * okontrolovat, ze sa nic nezmenilo a ze sme dostali rovnake data
   * ako predtym!
   */
  public static function hashNaOdhlasenie($mojeTerminyRow) {
    $data = array($mojeTerminyRow[MojeTerminyFields::INDEX],
                  $mojeTerminyRow[MojeTerminyFields::DATUM],
                  $mojeTerminyRow[MojeTerminyFields::CAS],
                  $mojeTerminyRow[MojeTerminyFields::PREDMET_SKRATKA]);
    return md5(implode('|', $data));
  }


  public static function hashNaPrihlasenie($predmetSkratka, $zoznamTerminovRow) {
    $data = array($zoznamTerminovRow[ZoznamTerminovFields::INDEX],
                  $zoznamTerminovRow[ZoznamTerminovFields::DATUM],
                  $zoznamTerminovRow[ZoznamTerminovFields::CAS],
                  $predmetSkratka);
    return md5(implode('|', $data));
  }

}
