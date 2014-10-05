<?php

namespace SocioChat\Utils;

/*
 * Определение наличия мата (нецензурных слов) в тексте, матотест
 *
 * Алгоритм достаточно надёжен и быстр, в т.ч. на больших объёмах данных
 * Метод обнаружения мата основывается на корнях и предлогах русского языка, а не на словаре
 * Слова "лох", "хер", "залупа", "сука" матерными словами не считаются (см. словарь Даля)
 * Разработка ведётся с 2005 года
 *
 * Класс явл. хорошим учебным пособием по изучению регулярных выражений и... русского мата! =)
 *
 * http://www.google.com/search?q=%F2%EE%EB%EA%EE%E2%FB%E9%20%F1%EB%EE%E2%E0%F0%FC%20%F0%F3%F1%F1%EA%EE%E3%EE%20%EC%E0%F2%E0&ie=cp1251&oe=UTF-8
 * http://www.awd.ru/dic.htm (Толковый словарь русского мата)
 *
 * Согласно статье 20.1 КоАП РФ нецензурная брань в общественных местах (интернет — место общественное) расценивается как мелкое хулиганство,
 * за что установлена административная ответственность — наложение штрафа в размере от пятисот до одной тысячи рублей или административный арест на срок до пятнадцати суток.
 *
 * TODO
 *   * добавить цифровую подделку с нулём под букву O
 *
 * @link     http://code.google.com/p/php-censure/
 * @license  http://creativecommons.org/licenses/by-sa/3.0/
 * @author   Nasibullin Rinat
 * @version  3.2.7
 */
class RudeFilter
{
    #запрещаем создание экземпляра класса, вызов методов этого класса только статически!
    private function __construct()
    {
    }

    /**
     *
     * @param    string $s строка для проверки
     * @param    int $delta ширина найденного фрагмента в словах
     *                                   (кол-во слов от матного слова слева и справа, максимально 10)
     * @param    string $continue строка, которая будет вставлена в начале и в конце фрагмента
     * @param    bool $is_html расценивать строку как HTML код?
     *                                   в режиме $is_html === TRUE html код игнорируется, а html сущности заменяются в "чистый" UTF-8
     * @param    string|null $replace строка, на которую заменять матный фрагмент, например: '[ой]' ($replace д.б. в кодировке $charset)
     *                                   опция работает в PHP >= 5.2.0
     * @param    string $charset кодировка символов (родная кодировка -- UTF-8, для других будет прозрачное перекодирование)
     * @return   bool|string|int|null    Если $replace === NULL, то возвращает FALSE, если мат не обнаружен, иначе фрагмент текста с матерным словом.
     *                                   Если $replace !== NULL, то возвращает исходную строку, где фрагменты мата заменены на $replace.
     *                                   В случае возникновения ошибки возвращает код ошибки > 0 (integer):
     *                                     * PREG_INTERNAL_ERROR
     *                                     * PREG_BACKTRACK_LIMIT_ERROR (see also pcre.backtrack_limit)
     *                                     * PREG_RECURSION_LIMIT_ERROR (see also pcre.recursion_limit)
     *                                     * PREG_BAD_UTF8_ERROR
     *                                     * PREG_BAD_UTF8_OFFSET_ERROR (since PHP 5.3.0)
     *                                   Или -1, если ReflectionTypeHint вернул ошибку
     */
    public static function parse(
        $s,
        $delta = 3,
        $continue = "\xe2\x80\xa6",
        $is_html = false,
        $replace = '*',
        $charset = 'UTF-8'
    ) {
        if (!ReflectionTypeHint::isValid()) {
            return -1;
        }
        if ($s === null) {
            return null;
        }

        static $re_badwords = null;

        if ($re_badwords === null) {
            #предлоги русского языка:
            #[всуо]|
            #по|за|на|об|до|от|вы|вс|вз|из|ис|
            #под|про|при|над|низ|раз|рас|воз|вос|
            #пооб|повы|пона|поза|недо|пере|одно|
            #полуза|произ|пораз|много|
            $pretext = array(
                #1
                '[уyоoаa]_?      (?=[еёeхx])',        #у, о   (уебать, охуеть, ахуеть)
                '[вvbсc]_?       (?=[хпбмгжxpmgj])',  #в, с   (впиздячить, схуярить)
                '[вvbсc]_?[ъь]_? (?=[еёe])',          #въ, съ (съебаться, въебать)
                'ё_?             (?=[бb6])',          #ё      (ёбля)
                #2
                '[вvb]_?[ыi]_?',      #вы
                '[зz3]_?[аa]_?',      #за
                '[нnh]_?[аaеeиi]_?',  #на, не, ни
                '[вvb]_?[сc]_?          (?=[хпбмгжxpmgj])',  #вс (вспизднуть)
                '[оo]_?[тtбb6]_?        (?=[хпбмгжxpmgj])',  #от, об
                '[оo]_?[тtбb6]_?[ъь]_?  (?=[еёe])',          #отъ, объ
                '[иiвvb]_?[зz3]_?       (?=[хпбмгжxpmgj])',  #[ив]з
                '[иiвvb]_?[зz3]_?[ъь]_? (?=[еёe])',          #[ив]зъ
                '[иi]_?[сc]_?           (?=[хпбмгжxpmgj])',  #ис
                '[пpдdg]_?[оo]_? (?> [бb6]_?         (?=[хпбмгжxpmgj])
                               | [бb6]_?  [ъь]_? (?=[еёe])
                               | [зz3]_? [аa] _?
                             )?',  #по, до, пообъ, дообъ, поза, доза (двойные символы вырезаются!)
                #3
                '[пp]_?[рr]_?[оoиi]_?',  #пр[ои]
                '[зz3]_?[лl]_?[оo]_?',   #зло (злоебучая)
                '[нnh]_?[аa]_?[дdg]_?         (?=[хпбмгжxpmgj])',  #над
                '[нnh]_?[аa]_?[дdg]_?[ъь]_?   (?=[еёe])',          #надъ
                '[пp]_?[оoаa]_?[дdg]_?        (?=[хпбмгжxpmgj])',  #под
                '[пp]_?[оoаa]_?[дdg]_?[ъь]_?  (?=[еёe])',          #подъ
                '[рr]_?[аa]_?[зz3сc]_?        (?=[хпбмгжxpmgj])',  #ра[зс]
                '[рr]_?[аa]_?[зz3сc]_?[ъь]_?  (?=[еёe])',          #ра[зс]ъ
                '[вvb]_?[оo]_?[зz3сc]_?       (?=[хпбмгжxpmgj])',  #во[зс]
                '[вvb]_?[оo]_?[зz3сc]_?[ъь]_? (?=[еёe])',          #во[зс]ъ
                #4
                '[нnh]_?[еe]_?[дdg]_?[оo]_?',    #недо
                '[пp]_?[еe]_?[рr]_?[еe]_?',      #пере
                '[oо]_?[дdg]_?[нnh]_?[оo]_?',    #одно
                '[кk]_?[oо]_?[нnh]_?[оo]_?',     #коно    (коноебиться)
                '[мm]_?[уy]_?[дdg]_?[oоaа]_?',   #муд[оа] (мудаёб)
                '[oо]_?[сc]_?[тt]_?[оo]_?',      #осто    (остопиздело)
                '[дdg]_?[уy]_?[рpr]_?[оoаa]_?',  #дур[оа]
                '[хx]_?[уy]_?[дdg]_?[оoаa]_?',   #худ[оа] (худоебина)
                #5
                '[мm]_?[нnh]_?[оo]_?[гg]_?[оo]_?',    #много
                '[мm]_?[оo]_?[рpr]_?[дdg]_?[оoаa]_?', #морд[оа]
                '[мm]_?[оo]_?[зz3]_?[гg]_?[оoаa]_?',  #мозг[оа]
                '[дdg]_?[оo]_?[лl]_?[бb6]_?[оoаa]_?', #долб[оа]
                '[оo]_?[сc]_?[тt]_?[рpr]_?[оo]_?',    #остро
            );

            $badwords = array(
                #Слово на букву Х
                '(?<=\PL) %RE_PRETEXT%?
                      [hхx]_?[уyu]_?[ийiеeёяюju]     #хуй, хуя, хую, хуем, хуёвый, охуительный
                      #исключения:
                      (?<! _hue(?=_)     #HUE     -- цветовая палитра
                         | _hue(?=so_)   #hueso   -- испанское слово
                         | _хуе(?=дин)   #Хуедин  -- город в Румынии
                         | _hyu(?=ndai_) #Hyundai -- марка корейского автомобиля
                      )',
                #Слово на букву П
                '(?<=\PL) %RE_PRETEXT%?
                      [пp]_?[иieеё]_?[зz3]_?[дd](?=_?[:vowel:])',
                #п[ие]зда, пизде, пиздёж, пизду, пиздюлина, пиздобол, опиздинеть, пиздых, подпёздывать

                #Слово на букву Е
                '(?<=\PL) %RE_PRETEXT%?
                      [eеё]_?
							#исключения
							(?<!н[eе][её]_|т_е_)    #неё, т.е. большие
                      [бb6]_? (?= [уyиi]_                       #ебу, еби
                                | [ыиiоoaаеeёуy]_?[:consonant:] #ебут, ебать, ебись, ебёт, поеботина, выебываться, ёбарь
                                   #исключения
                                  (?<!_ebo[kt](?=_)|буд)        #ebook, eboot, ее будут
                                | [лl](?:[оoаaыиiя]|ya)         #ебло, ебла, ебливая, еблись, еблысь, ёбля
                                | [нn]_?[уy]                    #ёбнул, ёбнутый
                                | [кk]_?[аa]                    #взъёбка
                                | [сc]_?[тt]                    #ебсти
                               )',
                #Слово на букву Е (c обязательной приставкой от 2-х и более букв!)
                '(?<=\PL) %RE_PRETEXT%
                      (?<= \pL\pL|\pL_\pL_)
                      [eеё]_?[бb6]    #долбоёб, дураёб, изъёб, заёб, заебай, разъебай, мудоёбы
            ',
                #Слово на букву Е
                '(?<=\PL) ёб (?=\PL)',
                #ёб твою мать

                #Слово на букву Б
                '(?<=\PL) %RE_PRETEXT%?
                      [бb6]_?[лl]_?(?:я|ya)(?: _         #бля
                                             | _?[тдtd]  #блять, бляди
                                           )',
                #ПИДОР
                '(?<=\PL) [пp]_?[иieе]_?[дdg]_?[eеaаoо]_?[rpр]',
                #п[ие]д[оеа]р

                #МУДАК
                '(?<=\PL) [мm]_?[уy]_?[дdg]_?[аa]  #мудак, мудачок
                      #исключения:
                      (?<!_myda(?=s_))  #Chelonia mydas -- морская зеленая (суповая) черепаха
            ',
//                #ЖОПА
                '(?<=\PL) [zж]_?h?_?[оo]_?[pп]_?[aаyуыiеeoо]',
                #жоп[ауыео]

                #МАНДА
                #исключения: город Мандалай, округ Мандаль, индейский народ Мандан, фамилия Мандель, мандарин
                '(?<=\PL) [мm]_?[аa]_?[нnh]_?[дdg]_?[aаyуыiеeoо]  #манд[ауыео]
                      #исключения:
                      (?<! манда(?=[лн]|рин)
                         | manda(?=[ln]|rin)
                         | манде(?=ль)
                      )',
//                #ГОВНО
                '(?<=\PL) [гg]_?[оo]_?[вvb]_?[нnh]_?[оoаaяеeyу]',
                #говн[оаяеу]

//                #FUCK
                '(?<=\PL) f_?u_?[cс]_?k',
                #fuck, fucking


//                #ЛОХ
//                ' л_?[оo]_?[хx]',

                #СУКА
//                '[^р]_?[scс]_?[yуu]_?[kк]_?[aаiи]', #сука (кроме слова "барсука" - это животное-грызун)
//                '[^р]_?[scс]_?[yуu]_?[4ч]_?[кk]',   #сучк(и) (кроме слова "барсучка")

//                #ХЕР
//                ' %RE_PRETEXT%?[хxh]_?[еe]_?[рpr](_?[нnh]_?(я|ya)| )', #%RE_PRETEXT%хер(ня)

                #ЗАЛУПА
                ' [зz3]_?[аa]_?[лl]_?[уy]_?[пp]_?[аa]',

            );

            $trans = array(
                '_' => '\x20',                       #пробел
                '\pL' => '[^\x20\d]',                  #буква
                '\PL' => '[\x20\d]',                   #не буква
                '[:vowel:]' => '[аеиоуыэюяёaeioyu]',         #гласные буквы
                '[:consonant:]' => '[^аеиоуыэюяёaeioyu\x20\d]',  #согласные буквы
            );

            $re_badwords = str_replace(
                '%RE_PRETEXT%',
                '(?:' . implode('|', $pretext) . ')',  #однократный шаблон с альтернативами использовать нельзя!
                '~' . implode('|', $badwords) . '~sxuSX'
            );
            $re_badwords = strtr($re_badwords, $trans);
        }

        $s = UTF8::convert_from($s, $charset);
        $replace = UTF8::convert_from($replace, $charset);

        $ss = $s;  #saves original string

        if ($is_html) {
            #скрипты не вырезаем, т.к. м.б. обходной маневр на с кодом на javascript:
            #<script>document.write('сло'+'во')</script>
            #хотя давать пользователю возможность использовать код на javascript нехорошо
            $s = is_callable(array('HTML', 'strip_tags')) ? HTML::strip_tags($s, null, true,
                array('comment', 'style', 'map', 'frameset', 'object', 'applet'))
                : strip_tags($s);
            #заменяем html-сущности в "чистый" UTF-8
            $s = UTF8::html_entity_decode($s, $is_htmlspecialchars = true);
        }

        if (strtoupper(substr($charset, 0, 3)) === 'UTF')  #UTF-8, UTF-16, UTF-32
        {
            #remove combining diactrical marks
            $additional_chars = array(
                "\xc2\xad",  #"мягкие" переносы строк (&shy;)
            );
            $s = UTF8::diactrical_remove($s, $additional_chars);
        }

        #ВотБ/\яПидорыОхуелиБлятьНахуйПохуйПи3децПолный
        if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
            $s = preg_replace('~     [\p{Lu}3] (?>\p{Ll}+|/\\\\|[@36]+)++   #Вот
								 (?= [\p{Lu}3] (?:\p{Ll} |/\\\\|[@36] ) )   #Бля
							   ~sxuSX', '$0 ', $s);
        }

        $s = UTF8::lowercase($s);

        #получаем в массив только буквы и цифры
        #"с_л@о#во,с\xc2\xa7лово.Слово" -> "с л о во с лово слово слово слово слово"
        preg_match_all('~(?> \xd0[\xb0-\xbf]|\xd1[\x80-\x8f\x91]  #[а-я]
						  |  /\\\\     #л
						  |  @         #а
						  |  [a-z\d]+
						  )+
						~sxSX', $s, $m);
        $s = ' ' . implode(' ', $m[0]) . ' ';

        $trans = array(
            '/\\' => 'л',  #Б/\ЯТЬ --> БЛЯТЬ
            '@' => 'а',  #пизд@  --> пизда
        );
        $s = strtr($s, $trans);

        #цифровые подделки под буквы
        $trans = array(
            '~ [3з]++ [3з\x20]*+ ~sxuSX' => 'з',
            '~ [6б]++ [6б\x20]*+ ~sxuSX' => 'б',
        );
        $s = preg_replace(array_keys($trans), array_values($trans), $s);

        #убираем все повторяющиеся символы, ловим обман типа "х-у-у-й"
        #"сллоооовоо   слово  х у у й" --> "слово слово х у й"
        $s = preg_replace('/(  [\xd0\xd1][\x80-\xbf] \x20?  #optimized [а-я]
                             | [a-z\d] \x20?
                             ) \\1+
                           /sxSX', '$1', $s);

        if ($replace === null || version_compare(PHP_VERSION, '5.2.0', '<')) {
            $result = preg_match($re_badwords, $s, $m, PREG_OFFSET_CAPTURE);
            if (function_exists('preg_last_error') && preg_last_error() !== PREG_NO_ERROR) {
                return preg_last_error();
            }
            if ($result === false) {
                return 1;
            }  #PREG_INTERNAL_ERROR = 1
            if ($result && $replace === null) {
                list($word, $offset) = $m[0];
                $s1 = substr($s, 0, $offset);
                $s2 = substr($s, $offset + strlen($word));
                $delta = intval($delta);
                if ($delta === 0) {
                    $fragment = '[' . trim($word) . ']';
                } else {
                    if ($delta < 1 || $delta > 10) {
                        $delta = 3;
                    }
                    preg_match('/  (?> \x20 (?>[\xd0\xd1][\x80-\xbf]|[a-z\d]+)++ ){1,' . $delta . '}+
                                   \x20?+
                                $/sxSX', $s1, $m1);
                    preg_match('/^ (?>[\xd0\xd1][\x80-\xbf]|[a-z\d]+)*+  #ending
                                   \x20?+
                                   (?> (?>[\xd0\xd1][\x80-\xbf]|[a-z\d]+)++ \x20 ){0,' . $delta . '}+
                                /sxSX', $s2, $m2);
                    $fragment = (ltrim(@$m1[0]) !== ltrim($s1) ? $continue : '') .
                        trim((isset($m1[0]) ? $m1[0] : '') . '[' . trim($word) . ']' . (isset($m2[0]) ? $m2[0] : '')) .
                        (rtrim(@$m2[0]) !== rtrim($s2) ? $continue : '');
                }
                return UTF8::convert_to($fragment, $charset);
            }
            return false;
        }

        $result = preg_match_all($re_badwords, $s, $m);
        if (function_exists('preg_last_error') && preg_last_error() !== PREG_NO_ERROR) {
            return preg_last_error();
        }
        if ($result === false) {
            return 1;
        }  #PREG_INTERNAL_ERROR = 1
        if ($result > 0) {
            #d($s, $m[0]);
            $s = $ss;
            #замена матного фрагмента на $replace
            foreach ($m[0] as $w) {
                $re_w = '~' . preg_replace_callback('~(?:/\\\\|[^\x20])~suSX', array('self', '_make_regexp_callback'),
                        $w) . '~sxuiSX';
                $ss = preg_replace($re_w, $replace, $ss);
                #d($re_w);
            }
            while ($ss !== $s) {
                $ss = self::parse($s = $ss, $delta, $continue, $is_html, $replace, 'UTF-8');
            }
        }
        return UTF8::convert_to($ss, $charset);
    }

    private static function _make_regexp_callback(array $m)
    {
        #$re_holes = '[\x00-\x20\-_\*\~\.\'"\^=`:]';
        #$re_holes = '[\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]';
        $re_holes = '(?!/\\\\)[^\p{L}\d]';  #non letter, non digit, non '/\'
        if ($m[0] === 'а') {
            $re = '[@аА]++           (?>[:holes:]|[@аА]+)*+';
        } elseif ($m[0] === 'з') {
            $re = '[3зЗ]++           (?>[:holes:]|[3зЗ]+)*+';
        } elseif ($m[0] === 'б') {
            $re = '[6бБ]++           (?>[:holes:]|[6бБ]+)*+';
        } elseif ($m[0] === 'л') {
            $re = '(?>[лЛ]+|/\\\\)++ (?>[:holes:]|[лЛ]+|/\\\\)*+';
        } else {
            #в PCRE-7.2 флаг /i в комбинации с /u в регулярном выражении почему-то не работает (BUG?)
            #поэтому делаем класс символов с буквами в обоих регистрах
            $char = '[' . preg_quote($m[0] . UTF8::uppercase($m[0]), '~') . ']';
            $re = str_replace('$0', $char, '$0++ (?>[:holes:]|$0+)*+');
        }
        return str_replace('[:holes:]', $re_holes, $re . "\r\n");
    }
}