<?php
/**
 * Locale
 *
 * @version    0.2 (2017-04-09 01:57:00 GMT)
 * @author     Peter Kahl <peter.kahl@colossalmind.com>
 * @copyright  2015-2017 Peter Kahl
 * @license    Apache License, Version 2.0
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      <http://www.apache.org/licenses/LICENSE-2.0>
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace peterkahl\locale;

class locale {

  /**
   * Version
   * @var string
   */
  const VERSION = '0.2';

  #===================================================================

  public static function country2locale($code) {
    # http://wiki.openstreetmap.org/wiki/Nominatim/Country_Codes
    $arr = array(
      'ad' => 'ca',
      'ae' => 'ar',
      'af' => 'fa,ps',
      'ag' => 'en',
      'ai' => 'en',
      'al' => 'sq',
      'am' => 'hy',
      'an' => 'nl,en',
      'ao' => 'pt',
      'aq' => 'en',
      'ar' => 'es',
      'as' => 'en,sm',
      'at' => 'de',
      'au' => 'en',
      'aw' => 'nl,pap',
      'ax' => 'sv',
      'az' => 'az',
      'ba' => 'bs,hr,sr',
      'bb' => 'en',
      'bd' => 'bn',
      'be' => 'nl,fr,de',
      'bf' => 'fr',
      'bg' => 'bg',
      'bh' => 'ar',
      'bi' => 'fr',
      'bj' => 'fr',
      'bl' => 'fr',
      'bm' => 'en',
      'bn' => 'ms',
      'bo' => 'es,qu,ay',
      'br' => 'pt',
      'bq' => 'nl,en',
      'bs' => 'en',
      'bt' => 'dz',
      'bv' => 'no',
      'bw' => 'en,tn',
      'by' => 'be,ru',
      'bz' => 'en',
      'ca' => 'en,fr',
      'cc' => 'en',
      'cd' => 'fr',
      'cf' => 'fr',
      'cg' => 'fr',
      'ch' => 'de,fr,it,rm',
      'ci' => 'fr',
      'ck' => 'en,rar',
      'cl' => 'es',
      'cm' => 'fr,en',
      'cn' => 'zh',
      'co' => 'es',
      'cr' => 'es',
      'cu' => 'es',
      'cv' => 'pt',
      'cw' => 'nl',
      'cx' => 'en',
      'cy' => 'el,tr',
      'cz' => 'cs',
      'de' => 'de',
      'dj' => 'fr,ar,so',
      'dk' => 'da',
      'dm' => 'en',
      'do' => 'es',
      'dz' => 'ar',
      'ec' => 'es',
      'ee' => 'et',
      'eg' => 'ar',
      'eh' => 'ar,es,fr',
      'er' => 'ti,ar,en',
      'es' => 'es,ast,ca,eu,gl',
      'et' => 'am,om',
      'fi' => 'fi,sv,se',
      'fj' => 'en',
      'fk' => 'en',
      'fm' => 'en',
      'fo' => 'fo',
      'fr' => 'fr',
      'ga' => 'fr',
      'gb' => 'en,ga,cy,gd,kw',
      'gd' => 'en',
      'ge' => 'ka',
      'gf' => 'fr',
      'gg' => 'en',
      'gh' => 'en',
      'gi' => 'en',
      'gl' => 'kl,da',
      'gm' => 'en',
      'gn' => 'fr',
      'gp' => 'fr',
      'gq' => 'es,fr,pt',
      'gr' => 'el',
      'gs' => 'en',
      'gt' => 'es',
      'gu' => 'en,ch',
      'gw' => 'pt',
      'gy' => 'en',
      'hk' => 'zh,en',
      'hm' => 'en',
      'hn' => 'es',
      'hr' => 'hr',
      'ht' => 'fr,ht',
      'hu' => 'hu',
      'id' => 'id',
      'ie' => 'en,ga',
      'il' => 'he',
      'im' => 'en',
      'in' => 'hi,en',
      'io' => 'en',
      'iq' => 'ar,ku',
      'ir' => 'fa',
      'is' => 'is',
      'it' => 'it,de,fr',
      'je' => 'en',
      'jm' => 'en',
      'jo' => 'ar',
      'jp' => 'ja',
      'ke' => 'sw,en',
      'kg' => 'ky,ru',
      'kh' => 'km',
      'ki' => 'en',
      'km' => 'ar,fr',
      'kn' => 'en',
      'kp' => 'ko',
      'kr' => 'ko,en',
      'kw' => 'ar',
      'ky' => 'en',
      'kz' => 'kk,ru',
      'la' => 'lo',
      'lb' => 'ar,fr',
      'lc' => 'en',
      'li' => 'de',
      'lk' => 'si,ta',
      'lr' => 'en',
      'ls' => 'en,st',
      'lt' => 'lt',
      'lu' => 'lb,fr,de',
      'lv' => 'lv',
      'ly' => 'ar',
      'ma' => 'ar',
      'mc' => 'fr',
      'md' => 'ru,uk,ro',
      'me' => 'srp,sq,bs,hr,sr',
      'mf' => 'fr',
      'mg' => 'mg,fr',
      'mh' => 'en,mh',
      'mk' => 'mk',
      'ml' => 'fr',
      'mm' => 'my',
      'mn' => 'mn',
      'mo' => 'zh,en,pt',
      'mp' => 'ch',
      'mq' => 'fr',
      'mr' => 'ar,fr',
      'ms' => 'en',
      'mt' => 'mt,en',
      'mu' => 'mfe,fr,en',
      'mv' => 'dv',
      'mw' => 'en,ny',
      'mx' => 'es',
      'my' => 'ms,zh,en',
      'mz' => 'pt',
      'na' => 'en,sf,de',
      'nc' => 'fr',
      'ne' => 'fr',
      'nf' => 'en,pih',
      'ng' => 'en',
      'ni' => 'es',
      'nl' => 'nl',
      'no' => 'nb,nn,no,se',
      'np' => 'ne',
      'nr' => 'na,en',
      'nu' => 'niu,en',
      'nz' => 'en,mi',
      'om' => 'ar',
      'pa' => 'es',
      'pe' => 'es',
      'pf' => 'fr',
      'pg' => 'en,tpi,ho',
      'ph' => 'en,tl',
      'pk' => 'en,ur',
      'pl' => 'pl',
      'pm' => 'fr',
      'pn' => 'en,pih',
      'pr' => 'es,en',
      'ps' => 'ar,he',
      'pt' => 'pt',
      'pw' => 'en,pau,ja,sov,tox',
      'py' => 'es,gn',
      'qa' => 'ar',
      're' => 'fr',
      'ro' => 'ro',
      'rs' => 'sr',
      'ru' => 'ru',
      'rw' => 'rw,fr,en',
      'sa' => 'ar',
      'sb' => 'en',
      'sc' => 'fr,en,crs',
      'sd' => 'ar,en',
      'se' => 'sv',
      'sg' => 'en,ms,zh,ta',
      'sh' => 'en',
      'si' => 'sl',
      'sj' => 'no',
      'sk' => 'sk',
      'sl' => 'en',
      'sm' => 'it',
      'sn' => 'fr',
      'so' => 'so,ar',
      'sr' => 'nl',
      'st' => 'pt',
      'ss' => 'en',
      'sv' => 'es',
      'sx' => 'nl,en',
      'sy' => 'ar',
      'sz' => 'en,ss',
      'tc' => 'en',
      'td' => 'fr,ar',
      'tf' => 'fr',
      'tg' => 'fr',
      'th' => 'th',
      'tj' => 'tg,ru',
      'tk' => 'tkl,en,sm',
      'tl' => 'pt,tet',
      'tm' => 'tk',
      'tn' => 'ar',
      'to' => 'en',
      'tr' => 'tr',
      'tt' => 'en',
      'tv' => 'en',
      'tw' => 'zh',
      'tz' => 'sw,en',
      'ua' => 'uk',
      'ug' => 'en,sw',
      'um' => 'en',
      'us' => 'en,es',
      'uy' => 'es',
      'uz' => 'uz,kaa',
      'va' => 'it',
      'vc' => 'en',
      've' => 'es',
      'vg' => 'en',
      'vi' => 'en',
      'vn' => 'vi',
      'vu' => 'bi,en,fr',
      'wf' => 'fr',
      'ws' => 'sm,en',
      'ye' => 'ar',
      'yt' => 'fr',
      'za' => 'zu,xh,af,st,tn,en',
      'zm' => 'en',
      'zw' => 'en,sn,nd'
    );
    #----
    $code = strtolower($code);
    if ($code == 'eu') {
      return 'en_GB';
    }
    elseif ($code == 'ap') { # Asia Pacific
      return 'en_US';
    }
    elseif ($code == 'cs') {
       return 'sr_RS';
    }
    #----
    if ($code == 'uk') {
      $code = 'gb';
    }
    if (array_key_exists($code, $arr)) {
      if (strpos($arr[$code], ',') !== false) {
        $new = explode(',', $arr[$code]);
        $loc = array();
        foreach ($new as $key => $val) {
          $loc[] = $val.'_'.strtoupper($code);
        }
        return implode(',', $loc); # string; comma-separated values 'en_GB,ga_GB,cy_GB,gd_GB,kw_GB'
      }
      else {
        return $arr[$code].'_'.strtoupper($code); # string 'en_US'
      }
    }
    return 'en_US';
  }

  #===================================================================
}