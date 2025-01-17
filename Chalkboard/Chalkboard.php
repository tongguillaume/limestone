<?php

namespace Chalkboard;
require_once __DIR__ . '/../vendor/autoload.php';
use Html2Text\Html2Text;
use PHPUnit\Util\Exception;


class Chalkboard
{
    public function getFolderFiles($path)
    {
        $arrayRes = [];

        if (is_dir($path)) {
            $scanned_directory = array_diff(scandir($path), array('..', '.'));
            foreach ($scanned_directory as $filename) {
                if(is_dir($path . "/" .$filename)) {
                    $testRecursion = $this->getFolderFiles($path . "/" .$filename);
                    $arrayRes = array_merge($arrayRes, $testRecursion);
                } else {
                    $arrayRes[] = $path . "/" . $filename;
                }
            }
        }

        return $arrayRes;
    }

    public function parseFile($path) //
    {
        $arrayRes = [];

        if (file_exists($path)) {
            $fileContent = file_get_contents($path);

            $content = new Html2Text($fileContent);
            $arrayContent = explode("\n", $content->getText());
            $stringLine = "";

            foreach ($arrayContent as $line) {
                if ($line != "") {
                    if ($stringLine != "") {
                        $arrayRes[] = $stringLine;
                        $stringLine = "";
                    }
                    $stringLine .= $line;
                } else {
                    $arrayRes[] = $stringLine;
                    $stringLine = "";
                }
            }
            $arrayRes[] = $stringLine;
            $arrayRes = self::parseLine($arrayRes);

        }
        return $arrayRes;

    }

    public function deletePartOfLine($line, $elem, $lastOrFirst)
    {
        if (strstr($line, $elem) === false)
            return $line;
      return strstr($line, $elem, $lastOrFirst);
    }


    public function parseLine($arrayLine)
    {
        $arrayRes = [];
        foreach ($arrayLine as $line) {
            $line = trim($line);
            $line = preg_replace("/{{.*?}}/", "", $line);
            $line = preg_replace("/{%.*?%}/", "", $line);
            $line = preg_replace("/{#.*?#}/", "", $line);
            $line = preg_replace("/\[.*?\]/", "", $line);
            $line = preg_replace("/form_.*?\)/", "", $line);
            $line = str_replace(" __ ", "", $line);
            $line = str_replace("__", "", $line);
            $line = str_replace("* ", "", $line);
            $line = self::deletePartOfLine($line, "{{", true);
            $line = self::deletePartOfLine($line, "form_", true);
            $line = self::deletePartOfLine($line, "{#", true);
            $line = self::deletePartOfLine($line, "{%", true);
            $line = self::deletePartOfLine($line, "}}", false);
            $line = self::deletePartOfLine($line, "%}", false);
            $line = self::deletePartOfLine($line, "#}", false);
            $line = str_replace("%}", "", $line);
            $line = str_replace("}} ) }}", "", $line);
            $line = str_replace("}}) }}", "", $line);
            $line = str_replace('"', "", $line);
            $line = str_replace('"', "", $line);
            $line = trim($line);

            if ($line != "" && preg_match("/[a-zA-Z]/i", $line)) {
                $arrayRes [] = $line;
            }
        }

        return $arrayRes;
    }

    public function htmlToXml($array)
    {
        file_put_contents("translate/messages.fr.xml",'<?xml version="1.0" encoding="UTF-8" ?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" datatype="plaintext" original="file.ext">
        <body>
     ');
        foreach ($array as $key => $line ) {
                $keyWord = self::getKeyWord($line);
                file_put_contents("translate/messages.fr.xml", '            <trans-unit id="'.strtolower($keyWord).'">'."\n",FILE_APPEND);
                file_put_contents("translate/messages.fr.xml", '                <source>'.strtolower($keyWord).'</source>'."\n",FILE_APPEND);
                file_put_contents("translate/messages.fr.xml", '                <target>'.$line.'</target>'."\n",FILE_APPEND);
                file_put_contents("translate/messages.fr.xml", '             </trans-unit>'."\n",FILE_APPEND);
        }

        file_put_contents("translate/messages.fr.xml",'        </body>
   </file>
</xliff>', FILE_APPEND
        );
    }

    public function remove_accent($str)
    {
      $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
                    'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã',
                    'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ',
                    'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ',
                    'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę',
                    'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī',
                    'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ',
                    'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ',
                    'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť',
                    'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ',
                    'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ',
                    'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');

      $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O',
                    'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c',
                    'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u',
                    'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D',
                    'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g',
                    'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K',
                    'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o',
                    'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S',
                    's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W',
                    'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i',
                    'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
      return str_replace($a, $b, $str);
    }

    public function getKeyWord($line)
    {
        $strToWordTab = explode(' ',trim($line));
        if (count($strToWordTab) == 1) {
            $keyWord = $strToWordTab[0];
        } else if (count($strToWordTab) > 1) {
            $keyWord = $strToWordTab[0].'_'.$strToWordTab[1];
        } else {
            $keyWord = "";
        }

        $keyWord = self::remove_accent($keyWord);
        return $keyWord;
    }

    public function htmlToYaml($array)
    {
        $previousKeyword = [];
        $cptKeyword = 0;
        foreach ($array as $key => $line) {
            $keyWord = strtolower(self::getKeyWord($line));
            if (in_array($keyWord, $previousKeyword)) {
                $keyWord = $keyWord . $cptKeyword;
                $cptKeyword++;
            }
            $previousKeyword[] = $keyWord;
            $line = str_replace("'", "", $line);
            //recreate the file
            if ($key == 0) {
                file_put_contents("translate/messages.fr.yaml", '"' . $keyWord . '": "' . $line . "\"\n");
            } else {
                file_put_contents("translate/messages.fr.yaml", '"' . $keyWord . '": "' . $line . "\"\n", FILE_APPEND);
            }
        }
    }


    public function createXMLandYaml($path)
    {
        $allFiles = self::getFolderFiles($path);
        $arrayLine = [];
        foreach ($allFiles as $file) {
            $arrayLine = array_merge($arrayLine, self::parseFile($file));
        }
        $arrayLine = array_intersect_key($arrayLine, array_unique(array_map('strtolower', $arrayLine)));
        self::htmlToYaml($arrayLine);
        self::htmlToXml($arrayLine);
    }
}
