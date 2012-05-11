{**
 *
 * @author G. Giunta
 * @copyright (C) G. Giunta 2012
 * @license Licensed under GNU General Public License v2.0. See file license.txt
 *}

{ezcss_require('xhprof.css')}

{* @todo load jquery tooltips and autocomplete *}
{ezscript_require( 'xhprof_report.js')}

<dl id="xhprofextrainfo">
<dd><b>URL</b> {$info.url|wash()}</dd>
<dd><b>Date</b> {$info.time|l10n('datetime')}</dd>
</dl>

{$body}