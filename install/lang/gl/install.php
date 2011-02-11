<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Automatically generated strings for Moodle installer
 *
 * Do not edit this file manually! It contains just a subset of strings
 * needed during the very first steps of installation. This file was
 * generated automatically by export-installer.php (which is part of AMOS
 * {@link http://docs.moodle.org/en/Development:Languages/AMOS}) using the
 * list of strings defined in /install/stringnames.txt.
 *
 * @package   installer
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['admindirname'] = 'Directorio de administración';
$string['availablelangs'] = 'Paquetes de idioma dispoñibles';
$string['chooselanguagehead'] = 'Escolla idioma';
$string['chooselanguagesub'] = 'Escolla un idioma SÓ para a instalación. Poderá escoller o idioma do sitio e o idioma de usuario en próximas pantallas.';
$string['dataroot'] = 'Directorio de datos';
$string['dbprefix'] = 'Prefixo das táboas';
$string['dirroot'] = 'Directorio Moodle';
$string['environmenthead'] = 'Verificando o seu ámbito ...';
$string['installation'] = 'Instalación';
$string['langdownloaderror'] = 'Desafortunadamente o idioma "{$a}" non foi instalado. O proceso de instalación continuará en inglés.';
$string['memorylimithelp'] = '<p>O límite de memoria para PHP do seu servidor está actualmente establecido en {$a}.</p>

<p>Isto fará que Moodle teña problemas de memoria máis tarde, especialmente se ten un número de módulos significativo e/ou un gran número de usuarios.</p>

<p>Recomendamos que configure PHP cun límite maior se é posible, como por exemplo 16M.  
   Existen varias formas que pode tentar para facer esta modificación:</p>
<ol>
<li>Se pode, recompile PHP con <i>--enable-memory-limit</i>.  
    Iso permitirá que o propio Moodle modifique o límite de memoria.</li>
<li>Se ten acceso ao seu ficheiro php.ini pode modificar o valor de <b>memory_limit</b> para algo semellante a 16M.  Se non ten acceso a ese ficheiro tal vez poida pedir ao administrador do sistema que o faga.</li>
<li>Nalgúns servidores PHP servers pode crear un ficheiro .htaccess no directorio Moodle coa liña seguinte:
    <p><blockquote>php_value memory_limit 16M</blockquote></p>
    <p>Porén, nalgúns servidores provocará que <b>non funcione ningunha</b>páxina en PHP (verá erros cando tente visualizar as páxinas) polo que terá que eliminar o ficheiro .htaccess.</p></li>
</ol>';
$string['phpversion'] = 'Versión PHP';
$string['phpversionhelp'] = '<p>Moodle require unha versión de PHP de 4.3.0 como mínimo ou 5.1.0 (5.0.x ten problemas coñecidos).</p>
<p>Actualmente está a executar a versión {$a}</p>
<p>Debe actualizar o PHP ou migrar a outro servidor cunha versión máis nova de PHP!<br/>
(No caso de ter unha versión 5.0.x pode retornar para unha versión 4.4.x)</p>';
$string['welcomep10'] = '{$a->installername} ({$a->installerversion})';
$string['welcomep20'] = 'Está a ver esta páxina porque conseguiu instalar e iniciar o  paquete <strong>{$a->packname} {$a->packversion}</strong> no seu computador. Parabéns!';
$string['welcomep30'] = 'Esta versión do <strong>{$a->installername}</strong> inclúe as aplicacións 
    para crear o ámbito en que <strong>Moodle</strong> pode funcionar, nomeadamente:';
$string['welcomep40'] = 'O paquete tamén inclúe <strong>Moodle {$a->moodlerelease} ({$a->moodleversion})</strong>.';
$string['welcomep50'] = 'A utilización de todas as aplicacións deste paquete réxese polas respectivas licenzas. O paquete <strong>{$a->installername}</strong> completo é     <a href="http://www.opensource.org/docs/definition_plain.html"> código aberto</a>  distribuído nos termos da licenza <a href="http://www.gnu.org/copyleft/gpl.html">GPL</a>.';
$string['welcomep60'] = 'As páxinas seguintes irán conducilo por algúns pasos fáciles   de seguir para configurar o <strong>Moodle</strong> no computador. Pode aceptar a configuración por defecto ou, opcionalmente, utilizar outras máis adecuadas ás súas necesidades.';
$string['welcomep70'] = 'Prema o botón "Seguinte" debaixo para continuar coa configuración de <strong>Moodle</strong>.';
$string['wwwroot'] = 'Enderezo web';
