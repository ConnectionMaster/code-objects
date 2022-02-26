<?php
/**
 * TransliterateTrait.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DevCoding\CodeObject\Object;

/**
 * Containing methods for unquoting and transliterating strings
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 * @package DevCoding\CodeObject\Object
 */
trait TransliterateTrait
{
  /**
   * Replace accent characters with their closes non-accented cousins; used as an alternative when the
   * intl extension isn't available or fails to transliterate the string.
   *
   * @param string $string String to replace characters in
   *
   * @return string string after character replacement
   */
  protected function transliterateFallback($string)
  {
    $transliteration = [
      '/À/' => 'A',       '/Á/' => 'A',       '/Â/' => 'A',       '/Ã/' => 'A',       '/Ä/' => 'Ae',
      '/Å/' => 'A',       '/Ā/' => 'A',       '/Ą/' => 'A',       '/Ă/' => 'A',       '/Æ/' => 'Ae',
      '/Ç/' => 'C',       '/Ć/' => 'C',       '/Č/' => 'C',       '/Ĉ/' => 'C',       '/Ċ/' => 'C',
      '/Ď/' => 'D',       '/Đ/' => 'D',       '/Ð/' => 'D',       '/È/' => 'E',       '/É/' => 'E',
      '/Ê/' => 'E',       '/Ë/' => 'E',       '/Ē/' => 'E',       '/Ę/' => 'E',       '/Ě/' => 'E',
      '/Ĕ/' => 'E',       '/Ė/' => 'E',       '/Ĝ/' => 'G',       '/Ğ/' => 'G',       '/Ġ/' => 'G',
      '/Ģ/' => 'G',       '/Ĥ/' => 'H',       '/Ħ/' => 'H',       '/Ì/' => 'I',       '/Í/' => 'I',
      '/Î/' => 'I',       '/Ï/' => 'I',       '/Ī/' => 'I',       '/Ĩ/' => 'I',       '/Ĭ/' => 'I',
      '/Į/' => 'I',       '/İ/' => 'I',       '/Ĳ/' => 'Ij',      '/Ĵ/' => 'J',       '/Ķ/' => 'K',
      '/Ł/' => 'L',       '/Ľ/' => 'L',       '/Ĺ/' => 'L',       '/Ļ/' => 'L',       '/Ŀ/' => 'L',
      '/Ñ/' => 'N',       '/Ń/' => 'N',       '/Ň/' => 'N',       '/Ņ/' => 'N',       '/Ŋ/' => 'N',
      '/Ò/' => 'O',       '/Ó/' => 'O',       '/Ô/' => 'O',       '/Õ/' => 'O',       '/Ö/' => 'Oe',
      '/Ø/' => 'O',       '/Ō/' => 'O',       '/Ő/' => 'O',       '/Ŏ/' => 'O',       '/Œ/' => 'Oe',
      '/Ŕ/' => 'R',       '/Ř/' => 'R',       '/Ŗ/' => 'R',       '/Ś/' => 'S',       '/Š/' => 'S',
      '/Ş/' => 'S',       '/Ŝ/' => 'S',       '/Ș/' => 'S',       '/Ť/' => 'T',       '/Ţ/' => 'T',
      '/Ŧ/' => 'T',       '/Ț/' => 'T',       '/Ù/' => 'U',       '/Ú/' => 'U',       '/Û/' => 'U',
      '/Ü/' => 'Ue',      '/Ū/' => 'U',       '/Ů/' => 'U',       '/Ű/' => 'U',       '/Ŭ/' => 'U',
      '/Ũ/' => 'U',       '/Ų/' => 'U',       '/Ŵ/' => 'W',       '/Ý/' => 'Y',       '/Ŷ/' => 'Y',
      '/Ÿ/' => 'Y',       '/Y/' => 'Y',       '/Ź/' => 'Z',       '/Ž/' => 'Z',       '/Ż/' => 'Z',
      '/Þ/' => 'T',
      '/à/' => 'a',       '/á/' => 'a',       '/â/' => 'a',       '/ã/' => 'a',       '/ä/' => 'ae',
      '/å/' => 'a',       '/ā/' => 'a',       '/ą/' => 'a',       '/ă/' => 'a',       '/æ/' => 'ae',
      '/ç/' => 'c',       '/ć/' => 'c',       '/č/' => 'c',       '/ĉ/' => 'c',       '/ċ/' => 'c',
      '/ď/' => 'd',       '/đ/' => 'd',       '/ð/' => 'd',       '/è/' => 'e',       '/é/' => 'e',
      '/ê/' => 'e',       '/ë/' => 'e',       '/ē/' => 'e',       '/ę/' => 'e',       '/ě/' => 'e',
      '/ĕ/' => 'e',       '/ė/' => 'e',       '/ĝ/' => 'g',       '/ğ/' => 'g',       '/ġ/' => 'g',
      '/ģ/' => 'g',       '/ĥ/' => 'h',       '/ħ/' => 'h',       '/ì/' => 'i',       '/í/' => 'i',
      '/î/' => 'i',       '/ï/' => 'i',       '/ī/' => 'i',       '/ĩ/' => 'i',       '/ĭ/' => 'i',
      '/į/' => 'i',       '/ı/' => 'i',       '/ĳ/' => 'ij',      '/ĵ/' => 'j',       '/ķ/' => 'k',
      '/ł/' => 'l',       '/ľ/' => 'l',       '/ĺ/' => 'l',       '/ļ/' => 'l',       '/ŀ/' => 'l',
      '/ñ/' => 'n',       '/ń/' => 'n',       '/ň/' => 'n',       '/ņ/' => 'n',       '/ŋ/' => 'n',
      '/ò/' => 'o',       '/ó/' => 'o',       '/ô/' => 'o',       '/õ/' => 'o',       '/ö/' => 'oe',
      '/ø/' => 'o',       '/ō/' => 'o',       '/ő/' => 'o',       '/ŏ/' => 'o',       '/œ/' => 'oe',
      '/ŕ/' => 'r',       '/ř/' => 'r',       '/ŗ/' => 'r',       '/ś/' => 's',       '/š/' => 's',
      '/ş/' => 's',       '/ŝ/' => 's',       '/ș/' => 's',       '/ť/' => 't',       '/ţ/' => 't',
      '/ŧ/' => 't',       '/ț/' => 't',       '/ù/' => 'u',       '/ú/' => 'u',       '/û/' => 'u',
      '/ü/' => 'ue',      '/ū/' => 'u',       '/ů/' => 'u',       '/ű/' => 'u',       '/ŭ/' => 'u',
      '/ũ/' => 'u',       '/ų/' => 'u',       '/ŵ/' => 'w',       '/ý/' => 'y',       '/ŷ/' => 'y',
      '/ÿ/' => 'y',       '/y/' => 'y',       '/ź/' => 'z',       '/ž/' => 'z',       '/ż/' => 'z',
      '/þ/' => 't',       '/ß/' => 'ss',      '/ſ/' => 'ss',      '/ƒ/' => 'f',       '/ĸ/' => 'k',
      '/ŉ/' => 'n',
    ];

    foreach ($transliteration as $key => $value)
    {
      $string = preg_replace($key, $value, $string);
    }

    return $string;
  }

  /**
   * Removes and replaces accent characters.
   *
   * @return string the transliterated word or phrase
   */
  protected function transliterateString($str)
  {
    if (function_exists('transliterator_transliterate'))
    {
      try
      {
        return transliterator_transliterate( 'Any-Latin; Latin-ASCII', $str );
      }
      catch ( \Exception $e )
      {
        return $this->transliterateFallback($str);
      }
    }

    return $this->transliterateFallback($str);
  }

  /**
   * Replaces single and double quote characters in the given string.
   *
   * @param string $string
   *
   * @return string
   */
  protected function unquoteString($string)
  {
    return preg_replace('#[\'"‘’”“]+]#', '', $string);
  }
}
