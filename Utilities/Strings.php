<?php declare(strict_types=1);
namespace Utilities;
use DOMDocument;

class Strings
{
    public function is_empty(string $string): bool
    {
        return (!empty($string) && !ctype_space($string) && mb_strlen(trim($string)) > 0 ? false : true);
    }

    public function convert(string $string): string
    {
        return trim(stripslashes(htmlspecialchars($string)));
    }

    public function random_string(int $length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function prepare_post_text(string $string): string
    {
        $string = strip_tags($string, ['br', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'b', 'i', 'u', 'a', 'strong', 'span', 'img', 'video']);
        preg_replace('/(<.+?)(?<=\s)on[a-z]+\s*=\s*(?:([\'"])(?!\2).+?\2|(?:\S+?\(.*?\)(?=[\s>])))(.*?>)/i', "$1 $3", $string);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($string, 'HTML-ENTITIES', "UTF-8"));
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        foreach($dom->getElementsByTagName('i') as $element){
            if($element->hasAttributes()){
                $element->remove();
            }
        }
        foreach($dom->getElementsByTagName('*') as $element){
            if($element->hasAttributes()){
                foreach($element->attributes as $attr){
                    $name = $attr->nodeName;
                    $value = $attr->nodeValue;
                    if($name == "class" && !in_array($value, ["content_quote", "content_dialogue"])){
                        $element->removeAttribute($name);
                    }
                    if($name == "href" && str_contains($value, "javascript:")){
                        $element->removeAttribute($name);
                    }
                    if($name == "style"){
                        $value = preg_replace('/\s+/','',$value);
                        if($value != 'color:rgb(0,0,0);' && $value != 'color:rgb(255,66,66);' && $value != 'color:rgb(255,136,0);' && $value != 'color:rgb(255,247,0);' && $value != 'color:rgb(0,159,0);' && $value != 'color:rgb(0,157,255);' && $value != 'color:rgb(0,38,255);' && $value != 'color:rgb(153,0,255);'){
                            $element->removeAttribute($name);
                        }
                    }
                    if($name == "src"){
                        $element->setAttribute('src', substr(BASE_URL, 0, -1).$value);
                    }
                    $element->removeAttribute("contenteditable");
                    if(!in_array($name, ["class", "href", "src", "style"])){
                        $element->removeAttribute($name);
                    }
                }
            }
        }
        return (!empty($string) ? preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML($dom->documentElement)) : "");
    }
}