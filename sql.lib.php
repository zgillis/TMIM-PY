<!DOCTYPE html>
<html><head>
<meta http-equiv="content-type" content="text/html; charset=windows-1252"><meta name="viewport" content="width=device-width"><title>http://bobcat.zgillis.com/sql.lib.phpx</title><link rel="stylesheet" type="text/css" href="resource://content-accessible/viewsource.css"></head><body id="viewsource" class="highlight" style="-moz-tab-size: 4" contextmenu="actions"><pre id="line1"><span></span><span class="error comment" title="Saw “&lt;?”. Probable cause: Attempt to use an XML processing instruction in HTML. (XML processing instructions are not supported in HTML.)">&lt;?php
<span id="line2"></span>/* vim: set expandtab sw=4 ts=4 sts=4: */
<span id="line3"></span>/**
<span id="line4"></span> * set of functions for the sql executor
<span id="line5"></span> *
<span id="line6"></span> * @package PhpMyAdmin
<span id="line7"></span> */
<span id="line8"></span>use PMA\libraries\DisplayResults;
<span id="line9"></span>use PMA\libraries\Message;
<span id="line10"></span>use PMA\libraries\Table;
<span id="line11"></span>use PMA\libraries\Response;
<span id="line12"></span>
<span id="line13"></span>/**
<span id="line14"></span> * Parses and analyzes the given SQL query.
<span id="line15"></span> *
<span id="line16"></span> * @param string $sql_query SQL query
<span id="line17"></span> * @param string $db        DB name
<span id="line18"></span> *
<span id="line19"></span> * @return mixed
<span id="line20"></span> */
<span id="line21"></span>function PMA_parseAndAnalyze($sql_query, $db = null)
<span id="line22"></span>{
<span id="line23"></span>    if (($db === null) &amp;&amp; (!empty($GLOBALS['db']))) {
<span id="line24"></span>        $db = $GLOBALS['db'];
<span id="line25"></span>    }
<span id="line26"></span>
<span id="line27"></span>    include_once 'libraries/parse_analyze.lib.php';
<span id="line28"></span>    list($analyzed_sql_results,,) = PMA_parseAnalyze($sql_query, $db);
<span id="line29"></span>
<span id="line30"></span>    return $analyzed_sql_results;
<span id="line31"></span>}
<span id="line32"></span>
<span id="line33"></span>/**
<span id="line34"></span> * Handle remembered sorting order, only for single table query
<span id="line35"></span> *
<span id="line36"></span> * @param string $db                    database name
<span id="line37"></span> * @param string $table                 table name
<span id="line38"></span> * @param array  &amp;$analyzed_sql_results the analyzed query results
<span id="line39"></span> * @param string &amp;$full_sql_query       SQL query
<span id="line40"></span> *
<span id="line41"></span> * @return void
<span id="line42"></span> */
<span id="line43"></span>function PMA_handleSortOrder(
<span id="line44"></span>    $db, $table, &amp;$analyzed_sql_results, &amp;$full_sql_query
<span id="line45"></span>) {
<span id="line46"></span>    $pmatable = new Table($table, $db);
<span id="line47"></span>
<span id="line48"></span>    if (empty($analyzed_sql_results['order'])) {
<span id="line49"></span>
<span id="line50"></span>        // Retrieving the name of the column we should sort after.
<span id="line51"></span>        $sortCol = $pmatable-&gt;</span><span>getUiProp(Table::PROP_SORTED_COLUMN);
<span id="line52"></span>        if (empty($sortCol)) {
<span id="line53"></span>            return;
<span id="line54"></span>        }
<span id="line55"></span>
<span id="line56"></span>        // Remove the name of the table from the retrieved field name.
<span id="line57"></span>        $sortCol = str_replace(
<span id="line58"></span>            PMA\libraries\Util::backquote($table) . '.',
<span id="line59"></span>            '',
<span id="line60"></span>            $sortCol
<span id="line61"></span>        );
<span id="line62"></span>
<span id="line63"></span>        // Create the new query.
<span id="line64"></span>        $full_sql_query = SqlParser\Utils\Query::replaceClause(
<span id="line65"></span>            $analyzed_sql_results['statement'],
<span id="line66"></span>            $analyzed_sql_results['parser']-&gt;list,
<span id="line67"></span>            'ORDER BY ' . $sortCol
<span id="line68"></span>        );
<span id="line69"></span>
<span id="line70"></span>        // TODO: Avoid reparsing the query.
<span id="line71"></span>        $analyzed_sql_results = SqlParser\Utils\Query::getAll($full_sql_query);
<span id="line72"></span>    } else {
<span id="line73"></span>        // Store the remembered table into session.
<span id="line74"></span>        $pmatable-&gt;setUiProp(
<span id="line75"></span>            Table::PROP_SORTED_COLUMN,
<span id="line76"></span>            SqlParser\Utils\Query::getClause(
<span id="line77"></span>                $analyzed_sql_results['statement'],
<span id="line78"></span>                $analyzed_sql_results['parser']-&gt;list,
<span id="line79"></span>                'ORDER BY'
<span id="line80"></span>            )
<span id="line81"></span>        );
<span id="line82"></span>    }
<span id="line83"></span>}
<span id="line84"></span>
<span id="line85"></span>/**
<span id="line86"></span> * Append limit clause to SQL query
<span id="line87"></span> *
<span id="line88"></span> * @param array <span><span class="error" title="“&amp;” did not start a character reference. (“&amp;” probably should have been escaped as “&amp;amp;”.)">&amp;</span></span>$analyzed_sql_results the analyzed query results
<span id="line89"></span> *
<span id="line90"></span> * @return string limit clause appended SQL query
<span id="line91"></span> */
<span id="line92"></span>function PMA_getSqlWithLimitClause(<span><span class="error" title="“&amp;” did not start a character reference. (“&amp;” probably should have been escaped as “&amp;amp;”.)">&amp;</span></span>$analyzed_sql_results)
<span id="line93"></span>{
<span id="line94"></span>    return SqlParser\Utils\Query::replaceClause(
<span id="line95"></span>        $analyzed_sql_results['statement'],
<span id="line96"></span>        $analyzed_sql_results['parser']-&gt;list,
<span id="line97"></span>        'LIMIT ' . $_SESSION['tmpval']['pos'] . ', '
<span id="line98"></span>        . $_SESSION['tmpval']['max_rows']
<span id="line99"></span>    );
<span id="line100"></span>}
<span id="line101"></span>
<span id="line102"></span>/**
<span id="line103"></span> * Verify whether the result set has columns from just one table
<span id="line104"></span> *
<span id="line105"></span> * @param array $fields_meta meta fields
<span id="line106"></span> *
<span id="line107"></span> * @return boolean whether the result set has columns from just one table
<span id="line108"></span> */
<span id="line109"></span>function PMA_resultSetHasJustOneTable($fields_meta)
<span id="line110"></span>{
<span id="line111"></span>    $just_one_table = true;
<span id="line112"></span>    $prev_table = '';
<span id="line113"></span>    foreach ($fields_meta as $one_field_meta) {
<span id="line114"></span>        if ($one_field_meta-&gt;table != ''
<span id="line115"></span>            <span><span>&amp;</span></span><span><span>&amp;</span></span> $prev_table != ''
<span id="line116"></span>            <span><span>&amp;</span></span><span><span>&amp;</span></span> $one_field_meta-&gt;table != $prev_table
<span id="line117"></span>        ) {
<span id="line118"></span>            $just_one_table = false;
<span id="line119"></span>        }
<span id="line120"></span>        if ($one_field_meta-&gt;table != '') {
<span id="line121"></span>            $prev_table = $one_field_meta-&gt;table;
<span id="line122"></span>        }
<span id="line123"></span>    }
<span id="line124"></span>    return $just_one_table <span><span>&amp;</span></span><span><span>&amp;</span></span> $prev_table != '';
<span id="line125"></span>}
<span id="line126"></span>
<span id="line127"></span>/**
<span id="line128"></span> * Verify whether the result set contains all the columns
<span id="line129"></span> * of at least one unique key
<span id="line130"></span> *
<span id="line131"></span> * @param string $db          database name
<span id="line132"></span> * @param string $table       table name
<span id="line133"></span> * @param array  $fields_meta meta fields
<span id="line134"></span> *
<span id="line135"></span> * @return boolean whether the result set contains a unique key
<span id="line136"></span> */
<span id="line137"></span>function PMA_resultSetContainsUniqueKey($db, $table, $fields_meta)
<span id="line138"></span>{
<span id="line139"></span>    $resultSetColumnNames = array();
<span id="line140"></span>    foreach ($fields_meta as $oneMeta) {
<span id="line141"></span>        $resultSetColumnNames[] = $oneMeta-&gt;name;
<span id="line142"></span>    }
<span id="line143"></span>    foreach (PMA\libraries\Index::getFromTable($table, $db) as $index) {
<span id="line144"></span>        if ($index-&gt;isUnique()) {
<span id="line145"></span>            $indexColumns = $index-&gt;getColumns();
<span id="line146"></span>            $numberFound = 0;
<span id="line147"></span>            foreach ($indexColumns as $indexColumnName =&gt; $dummy) {
<span id="line148"></span>                if (in_array($indexColumnName, $resultSetColumnNames)) {
<span id="line149"></span>                    $numberFound++;
<span id="line150"></span>                }
<span id="line151"></span>            }
<span id="line152"></span>            if ($numberFound == count($indexColumns)) {
<span id="line153"></span>                return true;
<span id="line154"></span>            }
<span id="line155"></span>        }
<span id="line156"></span>    }
<span id="line157"></span>    return false;
<span id="line158"></span>}
<span id="line159"></span>
<span id="line160"></span>/**
<span id="line161"></span> * Get the HTML for relational column dropdown
<span id="line162"></span> * During grid edit, if we have a relational field, returns the html for the
<span id="line163"></span> * dropdown
<span id="line164"></span> *
<span id="line165"></span> * @param string $db         current database
<span id="line166"></span> * @param string $table      current table
<span id="line167"></span> * @param string $column     current column
<span id="line168"></span> * @param string $curr_value current selected value
<span id="line169"></span> *
<span id="line170"></span> * @return string $dropdown html for the dropdown
<span id="line171"></span> */
<span id="line172"></span>function PMA_getHtmlForRelationalColumnDropdown($db, $table, $column, $curr_value)
<span id="line173"></span>{
<span id="line174"></span>    $foreigners = PMA_getForeigners($db, $table, $column);
<span id="line175"></span>
<span id="line176"></span>    $foreignData = PMA_getForeignData($foreigners, $column, false, '', '');
<span id="line177"></span>
<span id="line178"></span>    if ($foreignData['disp_row'] == null) {
<span id="line179"></span>        //Handle the case when number of values
<span id="line180"></span>        //is more than $cfg['ForeignKeyMaxLimit']
<span id="line181"></span>        $_url_params = array(
<span id="line182"></span>                'db' =&gt; $db,
<span id="line183"></span>                'table' =&gt; $table,
<span id="line184"></span>                'field' =&gt; $column
<span id="line185"></span>        );
<span id="line186"></span>
<span id="line187"></span>        $dropdown = '</span><span>&lt;<span class="start-tag">span</span> <span class="attribute-name">class</span>="<a class="attribute-value">curr_value</a>"&gt;</span><span>'
<span id="line188"></span>            . htmlspecialchars($_REQUEST['curr_value'])
<span id="line189"></span>            . '</span><span>&lt;/<span class="end-tag">span</span>&gt;</span><span>'
<span id="line190"></span>            . '</span><span class="error error error error error" title="No space between attributes.
Saw a quote when expecting an attribute name. Probable cause: “=” missing immediately before.
Quote in attribute name. Probable cause: Matching quote missing somewhere earlier.
Saw a quote when expecting an attribute name. Probable cause: “=” missing immediately before.
Quote in attribute name. Probable cause: Matching quote missing somewhere earlier.">&lt;<span class="start-tag">a</span> <span class="attribute-name">href</span>="<a class="attribute-value" href="view-source:http://bobcat.zgillis.com/browse_foreigners.php'%20%20%20%20%20%20%20%20%20%20%20%20.%20PMA_URL_getCommon($_url_params)%20.%20'">browse_foreigners.php'
<span id="line191"></span>            . PMA_URL_getCommon($_url_params) . '</a>"<span class="attribute-name">'</span>
<span id="line192"></span>            <span class="attribute-name">.</span> <span class="attribute-name">'class</span>="<a class="attribute-value">ajax browse_foreign</a>" <span class="attribute-name error" title="Duplicate attribute.">'</span> <span class="attribute-name error" title="Duplicate attribute.">.</span> <span class="attribute-name error" title="Duplicate attribute.">'</span>&gt;</span><span>'
<span id="line193"></span>            . __('Browse foreign values')
<span id="line194"></span>            . '</span><span>&lt;/<span class="end-tag">a</span>&gt;</span><span>';
<span id="line195"></span>    } else {
<span id="line196"></span>        $dropdown = PMA_foreignDropdown(
<span id="line197"></span>            $foreignData['disp_row'],
<span id="line198"></span>            $foreignData['foreign_field'],
<span id="line199"></span>            $foreignData['foreign_display'],
<span id="line200"></span>            $curr_value,
<span id="line201"></span>            $GLOBALS['cfg']['ForeignKeyMaxLimit']
<span id="line202"></span>        );
<span id="line203"></span>        $dropdown = '</span><span>&lt;<span class="start-tag">select</span>&gt;</span><span>' . $dropdown . '</span><span>&lt;/<span class="end-tag">select</span>&gt;</span><span>';
<span id="line204"></span>    }
<span id="line205"></span>
<span id="line206"></span>    return $dropdown;
<span id="line207"></span>}
<span id="line208"></span>
<span id="line209"></span>/**
<span id="line210"></span> * Get the HTML for the profiling table and accompanying chart if profiling is set.
<span id="line211"></span> * Otherwise returns null
<span id="line212"></span> *
<span id="line213"></span> * @param string $url_query         url query
<span id="line214"></span> * @param string $db                current database
<span id="line215"></span> * @param array  $profiling_results array containing the profiling info
<span id="line216"></span> *
<span id="line217"></span> * @return string $profiling_table html for the profiling table and chart
<span id="line218"></span> */
<span id="line219"></span>function PMA_getHtmlForProfilingChart($url_query, $db, $profiling_results)
<span id="line220"></span>{
<span id="line221"></span>    if (! empty($profiling_results)) {
<span id="line222"></span>        $pma_token = $_SESSION[' PMA_token '];
<span id="line223"></span>        $url_query = isset($url_query)
<span id="line224"></span>            ? $url_query
<span id="line225"></span>            : PMA_URL_getCommon(array('db' =&gt; $db));
<span id="line226"></span>
<span id="line227"></span>        $profiling_table = '';
<span id="line228"></span>        $profiling_table .= '</span><span>&lt;<span class="start-tag">fieldset</span>&gt;</span><span></span><span>&lt;<span class="start-tag">legend</span>&gt;</span><span>' . __('Profiling')
<span id="line229"></span>            . '</span><span>&lt;/<span class="end-tag">legend</span>&gt;</span><span>' . "\n";
<span id="line230"></span>        $profiling_table .= '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">floatleft</a>"&gt;</span><span>';
<span id="line231"></span>        $profiling_table .= '</span><span>&lt;<span class="start-tag">h3</span>&gt;</span><span>' . __('Detailed profile') . '</span><span>&lt;/<span class="end-tag">h3</span>&gt;</span><span>';
<span id="line232"></span>        $profiling_table .= '</span><span>&lt;<span class="start-tag">table</span> <span class="attribute-name">id</span>="<a class="attribute-value">profiletable</a>"&gt;</span><span></span><span>&lt;<span class="start-tag">thead</span>&gt;</span><span>' . "\n";
<span id="line233"></span>        $profiling_table .= ' </span><span class="error" title="Misplaced non-space characters inside a table.">&lt;<span class="start-tag">tr</span>&gt;</span><span>' . "\n";
<span id="line234"></span>        $profiling_table .= '  </span><span class="error" title="Misplaced non-space characters inside a table.">&lt;<span class="start-tag">th</span>&gt;</span><span>' . __('Order')
<span id="line235"></span>            . '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">sorticon</a>"&gt;</span><span></span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span></span><span>&lt;/<span class="end-tag">th</span>&gt;</span><span>' . "\n";
<span id="line236"></span>        $profiling_table .= '  </span><span class="error" title="Misplaced non-space characters inside a table.">&lt;<span class="start-tag">th</span>&gt;</span><span>' . __('State')
<span id="line237"></span>            . PMA\libraries\Util::showMySQLDocu('general-thread-states')
<span id="line238"></span>            . '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">sorticon</a>"&gt;</span><span></span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span></span><span>&lt;/<span class="end-tag">th</span>&gt;</span><span>' . "\n";
<span id="line239"></span>        $profiling_table .= '  </span><span class="error" title="Misplaced non-space characters inside a table.">&lt;<span class="start-tag">th</span>&gt;</span><span>' . __('Time')
<span id="line240"></span>            . '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">sorticon</a>"&gt;</span><span></span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span></span><span>&lt;/<span class="end-tag">th</span>&gt;</span><span>' . "\n";
<span id="line241"></span>        $profiling_table .= ' </span><span class="error" title="Misplaced non-space characters inside a table.">&lt;/<span class="end-tag">tr</span>&gt;</span><span></span><span>&lt;/<span class="end-tag">thead</span>&gt;</span><span></span><span>&lt;<span class="start-tag">tbody</span>&gt;</span><span>' . "\n";
<span id="line242"></span>        list($detailed_table, $chart_json, $profiling_stats)
<span id="line243"></span>            = PMA_analyzeAndGetTableHtmlForProfilingResults($profiling_results);
<span id="line244"></span>        $profiling_table .= $detailed_table;
<span id="line245"></span>        $profiling_table .= '</span><span class="error" title="Misplaced non-space characters inside a table.">&lt;/<span class="end-tag">tbody</span>&gt;</span><span></span><span>&lt;/<span class="end-tag">table</span>&gt;</span><span>' . "\n";
<span id="line246"></span>        $profiling_table .= '</span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span>';
<span id="line247"></span>
<span id="line248"></span>        $profiling_table .= '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">floatleft</a>"&gt;</span><span>';
<span id="line249"></span>        $profiling_table .= '</span><span>&lt;<span class="start-tag">h3</span>&gt;</span><span>' . __('Summary by state') . '</span><span>&lt;/<span class="end-tag">h3</span>&gt;</span><span>';
<span id="line250"></span>        $profiling_table .= '</span><span>&lt;<span class="start-tag">table</span> <span class="attribute-name">id</span>="<a class="attribute-value">profilesummarytable</a>"&gt;</span><span></span><span>&lt;<span class="start-tag">thead</span>&gt;</span><span>' . "\n";
<span id="line251"></span>        $profiling_table .= ' </span><span class="error" title="Misplaced non-space characters inside a table.">&lt;<span class="start-tag">tr</span>&gt;</span><span>' . "\n";
<span id="line252"></span>        $profiling_table .= '  </span><span class="error" title="Misplaced non-space characters inside a table.">&lt;<span class="start-tag">th</span>&gt;</span><span>' . __('State')
<span id="line253"></span>            . PMA\libraries\Util::showMySQLDocu('general-thread-states')
<span id="line254"></span>            . '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">sorticon</a>"&gt;</span><span></span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span></span><span>&lt;/<span class="end-tag">th</span>&gt;</span><span>' . "\n";
<span id="line255"></span>        $profiling_table .= '  </span><span class="error" title="Misplaced non-space characters inside a table.">&lt;<span class="start-tag">th</span>&gt;</span><span>' . __('Total Time')
<span id="line256"></span>            . '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">sorticon</a>"&gt;</span><span></span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span></span><span>&lt;/<span class="end-tag">th</span>&gt;</span><span>' . "\n";
<span id="line257"></span>        $profiling_table .= '  </span><span class="error" title="Misplaced non-space characters inside a table.">&lt;<span class="start-tag">th</span>&gt;</span><span>' . __('% Time')
<span id="line258"></span>            . '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">sorticon</a>"&gt;</span><span></span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span></span><span>&lt;/<span class="end-tag">th</span>&gt;</span><span>' . "\n";
<span id="line259"></span>        $profiling_table .= '  </span><span class="error" title="Misplaced non-space characters inside a table.">&lt;<span class="start-tag">th</span>&gt;</span><span>' . __('Calls')
<span id="line260"></span>            . '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">sorticon</a>"&gt;</span><span></span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span></span><span>&lt;/<span class="end-tag">th</span>&gt;</span><span>' . "\n";
<span id="line261"></span>        $profiling_table .= '  </span><span class="error" title="Misplaced non-space characters inside a table.">&lt;<span class="start-tag">th</span>&gt;</span><span>' . __('Ã¸ Time')
<span id="line262"></span>            . '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">sorticon</a>"&gt;</span><span></span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span></span><span>&lt;/<span class="end-tag">th</span>&gt;</span><span>' . "\n";
<span id="line263"></span>        $profiling_table .= ' </span><span class="error" title="Misplaced non-space characters inside a table.">&lt;/<span class="end-tag">tr</span>&gt;</span><span></span><span>&lt;/<span class="end-tag">thead</span>&gt;</span><span></span><span>&lt;<span class="start-tag">tbody</span>&gt;</span><span>' . "\n";
<span id="line264"></span>        $profiling_table .= PMA_getTableHtmlForProfilingSummaryByState(
<span id="line265"></span>            $profiling_stats
<span id="line266"></span>        );
<span id="line267"></span>        $profiling_table .= '</span><span class="error" title="Misplaced non-space characters inside a table.">&lt;/<span class="end-tag">tbody</span>&gt;</span><span></span><span>&lt;/<span class="end-tag">table</span>&gt;</span><span>' . "\n";
<span id="line268"></span>
<span id="line269"></span>        $profiling_table .= </span><span class="error" title="Bad character after “&lt;”. Probable cause: Unescaped “&lt;”. Try escaping it as “&amp;lt;”.">&lt;&lt;</span><span></span><span class="error" title="Bad character after “&lt;”. Probable cause: Unescaped “&lt;”. Try escaping it as “&amp;lt;”.">&lt;</span><span></span><span class="error" title="Saw “&lt;” when expecting an attribute name. Probable cause: Missing “&gt;” immediately before."><span class="start-tag">EOT</span>
<span id="line270"></span><span class="attribute-name">&lt;script</span> <span class="attribute-name">type</span>="<a class="attribute-value">text/javascript</a>"&gt;</span><span>
<span id="line271"></span>    pma_token = '$pma_token';
<span id="line272"></span>    url_query = '$url_query';
<span id="line273"></span></span><span class="error" title="Stray end tag “script”.">&lt;/<span class="end-tag">script</span>&gt;</span><span>
<span id="line274"></span>EOT;
<span id="line275"></span>        $profiling_table .= "</span><span class="error" title="End tag “div” seen, but there were open elements.">&lt;/<span class="end-tag">div</span>&gt;</span><span>";
<span id="line276"></span>        $profiling_table .= "</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>='<a class="attribute-value">clearfloat</a>'&gt;</span><span></span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span>";
<span id="line277"></span>
<span id="line278"></span>        //require_once 'libraries/chart.lib.php';
<span id="line279"></span>        $profiling_table .= '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">id</span>="<a class="attribute-value">profilingChartData</a>" <span class="attribute-name">style</span>="<a class="attribute-value">display:none;</a>"&gt;</span><span>';
<span id="line280"></span>        $profiling_table .= json_encode($chart_json);
<span id="line281"></span>        $profiling_table .= '</span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span>';
<span id="line282"></span>        $profiling_table .= '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">id</span>="<a class="attribute-value">profilingchart</a>" <span class="attribute-name">style</span>="<a class="attribute-value">display:none;</a>"&gt;</span><span>';
<span id="line283"></span>        $profiling_table .= '</span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span>';
<span id="line284"></span>        $profiling_table .= '</span><span>&lt;<span class="start-tag">script</span> <span class="attribute-name">type</span>="<a class="attribute-value">text/javascript</a>"&gt;</span><span>';
<span id="line285"></span>        $profiling_table .= "AJAX.registerOnload('sql.js', function () {";
<span id="line286"></span>        $profiling_table .= 'makeProfilingChart();';
<span id="line287"></span>        $profiling_table .= 'initProfilingTables();';
<span id="line288"></span>        $profiling_table .= '});';
<span id="line289"></span>        $profiling_table .= '</span><span>&lt;/<span class="end-tag">script</span>&gt;</span><span>';
<span id="line290"></span>        $profiling_table .= '</span><span>&lt;/<span class="end-tag">fieldset</span>&gt;</span><span>' . "\n";
<span id="line291"></span>    } else {
<span id="line292"></span>        $profiling_table = null;
<span id="line293"></span>    }
<span id="line294"></span>    return $profiling_table;
<span id="line295"></span>}
<span id="line296"></span>
<span id="line297"></span>/**
<span id="line298"></span> * Function to get HTML for detailed profiling results table, profiling stats, and
<span id="line299"></span> * $chart_json for displaying the chart.
<span id="line300"></span> *
<span id="line301"></span> * @param array $profiling_results profiling results
<span id="line302"></span> *
<span id="line303"></span> * @return mixed
<span id="line304"></span> */
<span id="line305"></span>function PMA_analyzeAndGetTableHtmlForProfilingResults(
<span id="line306"></span>    $profiling_results
<span id="line307"></span>) {
<span id="line308"></span>    $profiling_stats = array(
<span id="line309"></span>        'total_time' =&gt; 0,
<span id="line310"></span>        'states' =&gt; array(),
<span id="line311"></span>    );
<span id="line312"></span>    $chart_json = Array();
<span id="line313"></span>    $i = 1;
<span id="line314"></span>    $table = '';
<span id="line315"></span>    foreach ($profiling_results as $one_result) {
<span id="line316"></span>        if (isset($profiling_stats['states'][ucwords($one_result['Status'])])) {
<span id="line317"></span>            $states = $profiling_stats['states'];
<span id="line318"></span>            $states[ucwords($one_result['Status'])]['total_time']
<span id="line319"></span>                += $one_result['Duration'];
<span id="line320"></span>            $states[ucwords($one_result['Status'])]['calls']++;
<span id="line321"></span>        } else {
<span id="line322"></span>            $profiling_stats['states'][ucwords($one_result['Status'])] = array(
<span id="line323"></span>                'total_time' =&gt; $one_result['Duration'],
<span id="line324"></span>                'calls' =&gt; 1,
<span id="line325"></span>            );
<span id="line326"></span>        }
<span id="line327"></span>        $profiling_stats['total_time'] += $one_result['Duration'];
<span id="line328"></span>
<span id="line329"></span>        $table .= ' </span><span class="error" title="Stray start tag “tr”.">&lt;<span class="start-tag">tr</span>&gt;</span><span>' . "\n";
<span id="line330"></span>        $table .= '</span><span class="error" title="Stray start tag “td”.">&lt;<span class="start-tag">td</span>&gt;</span><span>' . $i++ . '</span><span class="error" title="Stray end tag “td”.">&lt;/<span class="end-tag">td</span>&gt;</span><span>' . "\n";
<span id="line331"></span>        $table .= '</span><span class="error" title="Stray start tag “td”.">&lt;<span class="start-tag">td</span>&gt;</span><span>' . ucwords($one_result['Status'])
<span id="line332"></span>            . '</span><span class="error" title="Stray end tag “td”.">&lt;/<span class="end-tag">td</span>&gt;</span><span>' . "\n";
<span id="line333"></span>        $table .= '</span><span class="error" title="Stray start tag “td”.">&lt;<span class="start-tag">td</span> <span class="attribute-name">class</span>="<a class="attribute-value">right</a>"&gt;</span><span>'
<span id="line334"></span>            . (PMA\libraries\Util::formatNumber($one_result['Duration'], 3, 1))
<span id="line335"></span>            . 's</span><span>&lt;<span class="start-tag">span</span> <span class="attribute-name">style</span>="<a class="attribute-value">display:none;</a>" <span class="attribute-name">class</span>="<a class="attribute-value">rawvalue</a>"&gt;</span><span>'
<span id="line336"></span>            . $one_result['Duration'] . '</span><span>&lt;/<span class="end-tag">span</span>&gt;</span><span></span><span class="error" title="Stray end tag “td”.">&lt;/<span class="end-tag">td</span>&gt;</span><span>' . "\n";
<span id="line337"></span>        if (isset($chart_json[ucwords($one_result['Status'])])) {
<span id="line338"></span>            $chart_json[ucwords($one_result['Status'])]
<span id="line339"></span>                += $one_result['Duration'];
<span id="line340"></span>        } else {
<span id="line341"></span>            $chart_json[ucwords($one_result['Status'])]
<span id="line342"></span>                = $one_result['Duration'];
<span id="line343"></span>        }
<span id="line344"></span>    }
<span id="line345"></span>    return array($table, $chart_json, $profiling_stats);
<span id="line346"></span>}
<span id="line347"></span>
<span id="line348"></span>/**
<span id="line349"></span> * Function to get HTML for summary by state table
<span id="line350"></span> *
<span id="line351"></span> * @param array $profiling_stats profiling stats
<span id="line352"></span> *
<span id="line353"></span> * @return string $table html for the table
<span id="line354"></span> */
<span id="line355"></span>function PMA_getTableHtmlForProfilingSummaryByState($profiling_stats)
<span id="line356"></span>{
<span id="line357"></span>    $table = '';
<span id="line358"></span>    foreach ($profiling_stats['states'] as $name =&gt; $stats) {
<span id="line359"></span>        $table .= ' </span><span class="error" title="Stray start tag “tr”.">&lt;<span class="start-tag">tr</span>&gt;</span><span>' . "\n";
<span id="line360"></span>        $table .= '</span><span class="error" title="Stray start tag “td”.">&lt;<span class="start-tag">td</span>&gt;</span><span>' . $name . '</span><span class="error" title="Stray end tag “td”.">&lt;/<span class="end-tag">td</span>&gt;</span><span>' . "\n";
<span id="line361"></span>        $table .= '</span><span class="error" title="Stray start tag “td”.">&lt;<span class="start-tag">td</span> <span class="attribute-name">align</span>="<a class="attribute-value">right</a>"&gt;</span><span>'
<span id="line362"></span>            . PMA\libraries\Util::formatNumber($stats['total_time'], 3, 1)
<span id="line363"></span>            . 's</span><span>&lt;<span class="start-tag">span</span> <span class="attribute-name">style</span>="<a class="attribute-value">display:none;</a>" <span class="attribute-name">class</span>="<a class="attribute-value">rawvalue</a>"&gt;</span><span>'
<span id="line364"></span>            . $stats['total_time'] . '</span><span>&lt;/<span class="end-tag">span</span>&gt;</span><span></span><span class="error" title="Stray end tag “td”.">&lt;/<span class="end-tag">td</span>&gt;</span><span>' . "\n";
<span id="line365"></span>        $table .= '</span><span class="error" title="Stray start tag “td”.">&lt;<span class="start-tag">td</span> <span class="attribute-name">align</span>="<a class="attribute-value">right</a>"&gt;</span><span>'
<span id="line366"></span>            . PMA\libraries\Util::formatNumber(
<span id="line367"></span>                100 * ($stats['total_time'] / $profiling_stats['total_time']),
<span id="line368"></span>                0, 2
<span id="line369"></span>            )
<span id="line370"></span>        . '%</span><span class="error" title="Stray end tag “td”.">&lt;/<span class="end-tag">td</span>&gt;</span><span>' . "\n";
<span id="line371"></span>        $table .= '</span><span class="error" title="Stray start tag “td”.">&lt;<span class="start-tag">td</span> <span class="attribute-name">align</span>="<a class="attribute-value">right</a>"&gt;</span><span>' . $stats['calls'] . '</span><span class="error" title="Stray end tag “td”.">&lt;/<span class="end-tag">td</span>&gt;</span><span>'
<span id="line372"></span>            . "\n";
<span id="line373"></span>        $table .= '</span><span class="error" title="Stray start tag “td”.">&lt;<span class="start-tag">td</span> <span class="attribute-name">align</span>="<a class="attribute-value">right</a>"&gt;</span><span>'
<span id="line374"></span>            . PMA\libraries\Util::formatNumber(
<span id="line375"></span>                $stats['total_time'] / $stats['calls'], 3, 1
<span id="line376"></span>            )
<span id="line377"></span>            . 's</span><span>&lt;<span class="start-tag">span</span> <span class="attribute-name">style</span>="<a class="attribute-value">display:none;</a>" <span class="attribute-name">class</span>="<a class="attribute-value">rawvalue</a>"&gt;</span><span>'
<span id="line378"></span>            . number_format($stats['total_time'] / $stats['calls'], 8, '.', '')
<span id="line379"></span>            . '</span><span>&lt;/<span class="end-tag">span</span>&gt;</span><span></span><span class="error" title="Stray end tag “td”.">&lt;/<span class="end-tag">td</span>&gt;</span><span>' . "\n";
<span id="line380"></span>        $table .= ' </span><span class="error" title="Stray end tag “tr”.">&lt;/<span class="end-tag">tr</span>&gt;</span><span>' . "\n";
<span id="line381"></span>    }
<span id="line382"></span>    return $table;
<span id="line383"></span>}
<span id="line384"></span>
<span id="line385"></span>/**
<span id="line386"></span> * Get the HTML for the enum column dropdown
<span id="line387"></span> * During grid edit, if we have a enum field, returns the html for the
<span id="line388"></span> * dropdown
<span id="line389"></span> *
<span id="line390"></span> * @param string $db         current database
<span id="line391"></span> * @param string $table      current table
<span id="line392"></span> * @param string $column     current column
<span id="line393"></span> * @param string $curr_value currently selected value
<span id="line394"></span> *
<span id="line395"></span> * @return string $dropdown html for the dropdown
<span id="line396"></span> */
<span id="line397"></span>function PMA_getHtmlForEnumColumnDropdown($db, $table, $column, $curr_value)
<span id="line398"></span>{
<span id="line399"></span>    $values = PMA_getValuesForColumn($db, $table, $column);
<span id="line400"></span>    $dropdown = '</span><span>&lt;<span class="start-tag">option</span> <span class="attribute-name">value</span>="<a class="attribute-value"></a>"&gt;</span><span><span class="entity"><span>&amp;</span>nbsp;</span></span><span>&lt;/<span class="end-tag">option</span>&gt;</span><span>';
<span id="line401"></span>    $dropdown .= PMA_getHtmlForOptionsList($values, array($curr_value));
<span id="line402"></span>    $dropdown = '</span><span>&lt;<span class="start-tag">select</span>&gt;</span><span>' . $dropdown . '</span><span>&lt;/<span class="end-tag">select</span>&gt;</span><span>';
<span id="line403"></span>    return $dropdown;
<span id="line404"></span>}
<span id="line405"></span>
<span id="line406"></span>/**
<span id="line407"></span> * Get value of a column for a specific row (marked by $where_clause)
<span id="line408"></span> *
<span id="line409"></span> * @param string $db           current database
<span id="line410"></span> * @param string $table        current table
<span id="line411"></span> * @param string $column       current column
<span id="line412"></span> * @param string $where_clause where clause to select a particular row
<span id="line413"></span> *
<span id="line414"></span> * @return string with value
<span id="line415"></span> */
<span id="line416"></span>function PMA_getFullValuesForSetColumn($db, $table, $column, $where_clause)
<span id="line417"></span>{
<span id="line418"></span>    $result = $GLOBALS['dbi']-&gt;fetchSingleRow(
<span id="line419"></span>        "SELECT `$column` FROM `$db`.`$table` WHERE $where_clause"
<span id="line420"></span>    );
<span id="line421"></span>
<span id="line422"></span>    return $result[$column];
<span id="line423"></span>}
<span id="line424"></span>
<span id="line425"></span>/**
<span id="line426"></span> * Get the HTML for the set column dropdown
<span id="line427"></span> * During grid edit, if we have a set field, returns the html for the
<span id="line428"></span> * dropdown
<span id="line429"></span> *
<span id="line430"></span> * @param string $db         current database
<span id="line431"></span> * @param string $table      current table
<span id="line432"></span> * @param string $column     current column
<span id="line433"></span> * @param string $curr_value currently selected value
<span id="line434"></span> *
<span id="line435"></span> * @return string $dropdown html for the set column
<span id="line436"></span> */
<span id="line437"></span>function PMA_getHtmlForSetColumn($db, $table, $column, $curr_value)
<span id="line438"></span>{
<span id="line439"></span>    $values = PMA_getValuesForColumn($db, $table, $column);
<span id="line440"></span>    $dropdown = '';
<span id="line441"></span>    $full_values =
<span id="line442"></span>        isset($_REQUEST['get_full_values']) ? $_REQUEST['get_full_values'] : false;
<span id="line443"></span>    $where_clause =
<span id="line444"></span>        isset($_REQUEST['where_clause']) ? $_REQUEST['where_clause'] : null;
<span id="line445"></span>
<span id="line446"></span>    // If the $curr_value was truncated, we should
<span id="line447"></span>    // fetch the correct full values from the table
<span id="line448"></span>    if ($full_values <span><span>&amp;</span></span><span><span>&amp;</span></span> ! empty($where_clause)) {
<span id="line449"></span>        $curr_value = PMA_getFullValuesForSetColumn(
<span id="line450"></span>            $db, $table, $column, $where_clause
<span id="line451"></span>        );
<span id="line452"></span>    }
<span id="line453"></span>
<span id="line454"></span>    //converts characters of $curr_value to HTML entities
<span id="line455"></span>    $converted_curr_value = htmlentities(
<span id="line456"></span>        $curr_value, ENT_COMPAT, "UTF-8"
<span id="line457"></span>    );
<span id="line458"></span>
<span id="line459"></span>    $selected_values = explode(',', $converted_curr_value);
<span id="line460"></span>
<span id="line461"></span>    $dropdown .= PMA_getHtmlForOptionsList($values, $selected_values);
<span id="line462"></span>
<span id="line463"></span>    $select_size = (sizeof($values) &gt; 10) ? 10 : sizeof($values);
<span id="line464"></span>    $dropdown = '</span><span>&lt;<span class="start-tag">select</span> <span class="attribute-name">multiple</span>="<a class="attribute-value">multiple</a>" <span class="attribute-name">size</span>="<a class="attribute-value">' . $select_size . '</a>"&gt;</span><span>'
<span id="line465"></span>        . $dropdown . '</span><span>&lt;/<span class="end-tag">select</span>&gt;</span><span>';
<span id="line466"></span>
<span id="line467"></span>    return $dropdown;
<span id="line468"></span>}
<span id="line469"></span>
<span id="line470"></span>/**
<span id="line471"></span> * Get all the values for a enum column or set column in a table
<span id="line472"></span> *
<span id="line473"></span> * @param string $db     current database
<span id="line474"></span> * @param string $table  current table
<span id="line475"></span> * @param string $column current column
<span id="line476"></span> *
<span id="line477"></span> * @return array $values array containing the value list for the column
<span id="line478"></span> */
<span id="line479"></span>function PMA_getValuesForColumn($db, $table, $column)
<span id="line480"></span>{
<span id="line481"></span>    $field_info_query = $GLOBALS['dbi']-&gt;getColumnsSql($db, $table, $column);
<span id="line482"></span>
<span id="line483"></span>    $field_info_result = $GLOBALS['dbi']-&gt;fetchResult(
<span id="line484"></span>        $field_info_query,
<span id="line485"></span>        null,
<span id="line486"></span>        null,
<span id="line487"></span>        null,
<span id="line488"></span>        PMA\libraries\DatabaseInterface::QUERY_STORE
<span id="line489"></span>    );
<span id="line490"></span>
<span id="line491"></span>    $values = PMA\libraries\Util::parseEnumSetValues($field_info_result[0]['Type']);
<span id="line492"></span>
<span id="line493"></span>    return $values;
<span id="line494"></span>}
<span id="line495"></span>
<span id="line496"></span>/**
<span id="line497"></span> * Get HTML for options list
<span id="line498"></span> *
<span id="line499"></span> * @param array $values          set of values
<span id="line500"></span> * @param array $selected_values currently selected values
<span id="line501"></span> *
<span id="line502"></span> * @return string $options HTML for options list
<span id="line503"></span> */
<span id="line504"></span>function PMA_getHtmlForOptionsList($values, $selected_values)
<span id="line505"></span>{
<span id="line506"></span>    $options = '';
<span id="line507"></span>    foreach ($values as $value) {
<span id="line508"></span>        $options .= '</span><span class="error error error error" title="No space between attributes.
Saw a quote when expecting an attribute name. Probable cause: “=” missing immediately before.
No space between attributes.
Quote in attribute name. Probable cause: Matching quote missing somewhere earlier.">&lt;<span class="start-tag">option</span> <span class="attribute-name">value</span>="<a class="attribute-value">' . $value . '</a>"<span class="attribute-name">';</span>
<span id="line509"></span>        <span class="attribute-name">if</span> <span class="attribute-name">(in_array($value,</span> <span class="attribute-name">$selected_values,</span> <span class="attribute-name">true))</span> <span class="attribute-name">{</span>
<span id="line510"></span>            <span class="attribute-name">$options</span> <span class="attribute-name">.</span>= '<a class="attribute-value"> selected="selected" </a>'<span class="attribute-name">;</span>
<span id="line511"></span>        <span class="attribute-name">}</span>
<span id="line512"></span>        <span class="attribute-name error" title="Duplicate attribute.">$options</span> <span class="attribute-name error" title="Duplicate attribute.">.</span>= '<a class="attribute-value">&gt;</a>' <span class="attribute-name error" title="Duplicate attribute.">.</span> <span class="attribute-name">$value</span> <span class="attribute-name error" title="Duplicate attribute.">.</span> <span class="attribute-name error" title="“&lt;” in attribute name. Probable cause: “&gt;” missing immediately before.">'&lt;</span><span class="error" title="A slash was not immediately followed by “&gt;”.">/</span><span class="attribute-name">option</span>&gt;</span><span>';
<span id="line513"></span>    }
<span id="line514"></span>    return $options;
<span id="line515"></span>}
<span id="line516"></span>
<span id="line517"></span>/**
<span id="line518"></span> * Function to get html for bookmark support if bookmarks are enabled. Else will
<span id="line519"></span> * return null
<span id="line520"></span> *
<span id="line521"></span> * @param array  $displayParts   the parts to display
<span id="line522"></span> * @param array  $cfgBookmark    configuration setting for bookmarking
<span id="line523"></span> * @param string $sql_query      sql query
<span id="line524"></span> * @param string $db             current database
<span id="line525"></span> * @param string $table          current table
<span id="line526"></span> * @param string $complete_query complete query
<span id="line527"></span> * @param string $bkm_user       bookmarking user
<span id="line528"></span> *
<span id="line529"></span> * @return string $html
<span id="line530"></span> */
<span id="line531"></span>function PMA_getHtmlForBookmark($displayParts, $cfgBookmark, $sql_query, $db,
<span id="line532"></span>    $table, $complete_query, $bkm_user
<span id="line533"></span>) {
<span id="line534"></span>    if ($displayParts['bkm_form'] == '1'
<span id="line535"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> (! empty($cfgBookmark) <span><span>&amp;</span></span><span><span>&amp;</span></span> empty($_GET['id_bookmark']))
<span id="line536"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> ! empty($sql_query)
<span id="line537"></span>    ) {
<span id="line538"></span>        $goto = 'sql.php'
<span id="line539"></span>            . PMA_URL_getCommon(
<span id="line540"></span>                array(
<span id="line541"></span>                    'db' =&gt; $db,
<span id="line542"></span>                    'table' =&gt; $table,
<span id="line543"></span>                    'sql_query' =&gt; $sql_query,
<span id="line544"></span>                    'id_bookmark'=&gt; 1,
<span id="line545"></span>                )
<span id="line546"></span>            );
<span id="line547"></span>        $bkm_sql_query = isset($complete_query) ? $complete_query : $sql_query;
<span id="line548"></span>        $html = '</span><span class="error error error error error error" title="No space between attributes.
Saw a quote when expecting an attribute name. Probable cause: “=” missing immediately before.
Quote in attribute name. Probable cause: Matching quote missing somewhere earlier.
No space between attributes.
Saw a quote when expecting an attribute name. Probable cause: “=” missing immediately before.
Quote in attribute name. Probable cause: Matching quote missing somewhere earlier.">&lt;<span class="start-tag">form</span> <span class="attribute-name">action</span>="<a class="attribute-value" href="view-source:http://bobcat.zgillis.com/sql.php">sql.php</a>" <span class="attribute-name">method</span>="<a class="attribute-value">post</a>"<span class="attribute-name">'</span>
<span id="line549"></span>            <span class="attribute-name">.</span> <span class="attribute-name error" title="Duplicate attribute.">'</span> <span class="attribute-name">onsubmit</span>="<a class="attribute-value">return ! emptyCheckTheField(this,'
<span id="line550"></span>            . '\'bkm_fields[bkm_label]\');</a>"<span class="attribute-name error" title="Duplicate attribute.">'</span>
<span id="line551"></span>            <span class="attribute-name error" title="Duplicate attribute.">.</span> <span class="attribute-name error" title="Duplicate attribute.">'</span> <span class="attribute-name">class</span>="<a class="attribute-value">bookmarkQueryForm print_ignore</a>"&gt;</span><span>';
<span id="line552"></span>        $html .= PMA_URL_getHiddenInputs();
<span id="line553"></span>        $html .= '</span><span class="error error error" title="No space between attributes.
Saw a quote when expecting an attribute name. Probable cause: “=” missing immediately before.
Quote in attribute name. Probable cause: Matching quote missing somewhere earlier.">&lt;<span class="start-tag">input</span> <span class="attribute-name">type</span>="<a class="attribute-value">hidden</a>" <span class="attribute-name">name</span>="<a class="attribute-value">db</a>"<span class="attribute-name">'</span>
<span id="line554"></span>            <span class="attribute-name">.</span> <span class="attribute-name error" title="Duplicate attribute.">'</span> <span class="attribute-name">value</span>="<a class="attribute-value">' . htmlspecialchars($db) . '</a>" <span>/</span>&gt;</span><span>';
<span id="line555"></span>        $html .= '</span><span>&lt;<span class="start-tag">input</span> <span class="attribute-name">type</span>="<a class="attribute-value">hidden</a>" <span class="attribute-name">name</span>="<a class="attribute-value">goto</a>" <span class="attribute-name">value</span>="<a class="attribute-value">' . $goto . '</a>" <span>/</span>&gt;</span><span>';
<span id="line556"></span>        $html .= '</span><span class="error error error" title="No space between attributes.
Saw a quote when expecting an attribute name. Probable cause: “=” missing immediately before.
Quote in attribute name. Probable cause: Matching quote missing somewhere earlier.">&lt;<span class="start-tag">input</span> <span class="attribute-name">type</span>="<a class="attribute-value">hidden</a>" <span class="attribute-name">name</span>="<a class="attribute-value">bkm_fields[bkm_database]</a>"<span class="attribute-name">'</span>
<span id="line557"></span>            <span class="attribute-name">.</span> <span class="attribute-name error" title="Duplicate attribute.">'</span> <span class="attribute-name">value</span>="<a class="attribute-value">' . htmlspecialchars($db) . '</a>" <span>/</span>&gt;</span><span>';
<span id="line558"></span>        $html .= '</span><span class="error error error" title="No space between attributes.
Saw a quote when expecting an attribute name. Probable cause: “=” missing immediately before.
Quote in attribute name. Probable cause: Matching quote missing somewhere earlier.">&lt;<span class="start-tag">input</span> <span class="attribute-name">type</span>="<a class="attribute-value">hidden</a>" <span class="attribute-name">name</span>="<a class="attribute-value">bkm_fields[bkm_user]</a>"<span class="attribute-name">'</span>
<span id="line559"></span>            <span class="attribute-name">.</span> <span class="attribute-name error" title="Duplicate attribute.">'</span> <span class="attribute-name">value</span>="<a class="attribute-value">' . $bkm_user . '</a>" <span>/</span>&gt;</span><span>';
<span id="line560"></span>        $html .= '</span><span class="error error error" title="No space between attributes.
Saw a quote when expecting an attribute name. Probable cause: “=” missing immediately before.
Quote in attribute name. Probable cause: Matching quote missing somewhere earlier.">&lt;<span class="start-tag">input</span> <span class="attribute-name">type</span>="<a class="attribute-value">hidden</a>" <span class="attribute-name">name</span>="<a class="attribute-value">bkm_fields[bkm_sql_query]</a>"<span class="attribute-name">'</span>
<span id="line561"></span>            <span class="attribute-name">.</span> <span class="attribute-name error" title="Duplicate attribute.">'</span> <span class="attribute-name">value</span>="<a class="attribute-value">'
<span id="line562"></span>            . htmlspecialchars($bkm_sql_query)
<span id="line563"></span>            . '</a>" <span>/</span>&gt;</span><span>';
<span id="line564"></span>        $html .= '</span><span>&lt;<span class="start-tag">fieldset</span>&gt;</span><span>';
<span id="line565"></span>        $html .= '</span><span>&lt;<span class="start-tag">legend</span>&gt;</span><span>';
<span id="line566"></span>        $html .= PMA\libraries\Util::getIcon(
<span id="line567"></span>            'b_bookmark.png', __('Bookmark this SQL query'), true
<span id="line568"></span>        );
<span id="line569"></span>        $html .= '</span><span>&lt;/<span class="end-tag">legend</span>&gt;</span><span>';
<span id="line570"></span>        $html .= '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">formelement</a>"&gt;</span><span>';
<span id="line571"></span>        $html .= '</span><span>&lt;<span class="start-tag">label</span>&gt;</span><span>' . __('Label:');
<span id="line572"></span>        $html .= '</span><span>&lt;<span class="start-tag">input</span> <span class="attribute-name">type</span>="<a class="attribute-value">text</a>" <span class="attribute-name">name</span>="<a class="attribute-value">bkm_fields[bkm_label]</a>" <span class="attribute-name">value</span>="<a class="attribute-value"></a>" <span>/</span>&gt;</span><span>' .
<span id="line573"></span>            '</span><span>&lt;/<span class="end-tag">label</span>&gt;</span><span>';
<span id="line574"></span>        $html .= '</span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span>';
<span id="line575"></span>        $html .= '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">formelement</a>"&gt;</span><span>';
<span id="line576"></span>        $html .= '</span><span>&lt;<span class="start-tag">label</span>&gt;</span><span>' .
<span id="line577"></span>            '</span><span>&lt;<span class="start-tag">input</span> <span class="attribute-name">type</span>="<a class="attribute-value">checkbox</a>" <span class="attribute-name">name</span>="<a class="attribute-value">bkm_all_users</a>" <span class="attribute-name">value</span>="<a class="attribute-value">true</a>" <span>/</span>&gt;</span><span>';
<span id="line578"></span>        $html .=  __('Let every user access this bookmark') . '</span><span>&lt;/<span class="end-tag">label</span>&gt;</span><span>';
<span id="line579"></span>        $html .= '</span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span>';
<span id="line580"></span>        $html .= '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">clearfloat</a>"&gt;</span><span></span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span>';
<span id="line581"></span>        $html .= '</span><span>&lt;/<span class="end-tag">fieldset</span>&gt;</span><span>';
<span id="line582"></span>        $html .= '</span><span>&lt;<span class="start-tag">fieldset</span> <span class="attribute-name">class</span>="<a class="attribute-value">tblFooters</a>"&gt;</span><span>';
<span id="line583"></span>        $html .= '</span><span>&lt;<span class="start-tag">input</span> <span class="attribute-name">type</span>="<a class="attribute-value">hidden</a>" <span class="attribute-name">name</span>="<a class="attribute-value">store_bkm</a>" <span class="attribute-name">value</span>="<a class="attribute-value">1</a>" <span>/</span>&gt;</span><span>';
<span id="line584"></span>        $html .= '</span><span class="error error error" title="No space between attributes.
Saw a quote when expecting an attribute name. Probable cause: “=” missing immediately before.
Quote in attribute name. Probable cause: Matching quote missing somewhere earlier.">&lt;<span class="start-tag">input</span> <span class="attribute-name">type</span>="<a class="attribute-value">submit</a>"<span class="attribute-name">'</span>
<span id="line585"></span>            <span class="attribute-name">.</span> <span class="attribute-name error" title="Duplicate attribute.">'</span> <span class="attribute-name">value</span>="<a class="attribute-value">' . __('Bookmark this SQL query') . '</a>" <span>/</span>&gt;</span><span>';
<span id="line586"></span>        $html .= '</span><span>&lt;/<span class="end-tag">fieldset</span>&gt;</span><span>';
<span id="line587"></span>        $html .= '</span><span>&lt;/<span class="end-tag">form</span>&gt;</span><span>';
<span id="line588"></span>
<span id="line589"></span>    } else {
<span id="line590"></span>        $html = null;
<span id="line591"></span>    }
<span id="line592"></span>
<span id="line593"></span>    return $html;
<span id="line594"></span>}
<span id="line595"></span>
<span id="line596"></span>/**
<span id="line597"></span> * Function to check whether to remember the sorting order or not
<span id="line598"></span> *
<span id="line599"></span> * @param array $analyzed_sql_results the analyzed query and other variables set
<span id="line600"></span> *                                    after analyzing the query
<span id="line601"></span> *
<span id="line602"></span> * @return boolean
<span id="line603"></span> */
<span id="line604"></span>function PMA_isRememberSortingOrder($analyzed_sql_results)
<span id="line605"></span>{
<span id="line606"></span>    return $GLOBALS['cfg']['RememberSorting']
<span id="line607"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> ! ($analyzed_sql_results['is_count']
<span id="line608"></span>            || $analyzed_sql_results['is_export']
<span id="line609"></span>            || $analyzed_sql_results['is_func']
<span id="line610"></span>            || $analyzed_sql_results['is_analyse'])
<span id="line611"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> $analyzed_sql_results['select_from']
<span id="line612"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> ((empty($analyzed_sql_results['select_expr']))
<span id="line613"></span>            || (count($analyzed_sql_results['select_expr']) == 1)
<span id="line614"></span>                <span><span>&amp;</span></span><span><span>&amp;</span></span> ($analyzed_sql_results['select_expr'][0] == '*')))
<span id="line615"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> count($analyzed_sql_results['select_tables']) == 1;
<span id="line616"></span>}
<span id="line617"></span>
<span id="line618"></span>/**
<span id="line619"></span> * Function to check whether the LIMIT clause should be appended or not
<span id="line620"></span> *
<span id="line621"></span> * @param array $analyzed_sql_results the analyzed query and other variables set
<span id="line622"></span> *                                    after analyzing the query
<span id="line623"></span> *
<span id="line624"></span> * @return boolean
<span id="line625"></span> */
<span id="line626"></span>function PMA_isAppendLimitClause($analyzed_sql_results)
<span id="line627"></span>{
<span id="line628"></span>    // Assigning LIMIT clause to an syntactically-wrong query
<span id="line629"></span>    // is not needed. Also we would want to show the true query
<span id="line630"></span>    // and the true error message to the query executor
<span id="line631"></span>
<span id="line632"></span>    return (isset($analyzed_sql_results['parser'])
<span id="line633"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> count($analyzed_sql_results['parser']-&gt;errors) === 0)
<span id="line634"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> ($_SESSION['tmpval']['max_rows'] != 'all')
<span id="line635"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> ! ($analyzed_sql_results['is_export']
<span id="line636"></span>        || $analyzed_sql_results['is_analyse'])
<span id="line637"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> ($analyzed_sql_results['select_from']
<span id="line638"></span>            || $analyzed_sql_results['is_subquery'])
<span id="line639"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> empty($analyzed_sql_results['limit']);
<span id="line640"></span>}
<span id="line641"></span>
<span id="line642"></span>/**
<span id="line643"></span> * Function to check whether this query is for just browsing
<span id="line644"></span> *
<span id="line645"></span> * @param array   $analyzed_sql_results the analyzed query and other variables set
<span id="line646"></span> *                                      after analyzing the query
<span id="line647"></span> * @param boolean $find_real_end        whether the real end should be found
<span id="line648"></span> *
<span id="line649"></span> * @return boolean
<span id="line650"></span> */
<span id="line651"></span>function PMA_isJustBrowsing($analyzed_sql_results, $find_real_end)
<span id="line652"></span>{
<span id="line653"></span>    return ! $analyzed_sql_results['is_group']
<span id="line654"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> ! $analyzed_sql_results['is_func']
<span id="line655"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> empty($analyzed_sql_results['union'])
<span id="line656"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> empty($analyzed_sql_results['distinct'])
<span id="line657"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> $analyzed_sql_results['select_from']
<span id="line658"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> (count($analyzed_sql_results['select_tables']) === 1)
<span id="line659"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> (empty($analyzed_sql_results['statement']-&gt;where)
<span id="line660"></span>            || (count($analyzed_sql_results['statement']-&gt;where) == 1
<span id="line661"></span>                <span><span>&amp;</span></span><span><span>&amp;</span></span> $analyzed_sql_results['statement']-&gt;where[0]-&gt;expr ==='1'))
<span id="line662"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> empty($analyzed_sql_results['group'])
<span id="line663"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> ! isset($find_real_end)
<span id="line664"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> ! $analyzed_sql_results['is_subquery']
<span id="line665"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> ! $analyzed_sql_results['join']
<span id="line666"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> empty($analyzed_sql_results['having']);
<span id="line667"></span>}
<span id="line668"></span>
<span id="line669"></span>/**
<span id="line670"></span> * Function to check whether the related transformation information should be deleted
<span id="line671"></span> *
<span id="line672"></span> * @param array $analyzed_sql_results the analyzed query and other variables set
<span id="line673"></span> *                                    after analyzing the query
<span id="line674"></span> *
<span id="line675"></span> * @return boolean
<span id="line676"></span> */
<span id="line677"></span>function PMA_isDeleteTransformationInfo($analyzed_sql_results)
<span id="line678"></span>{
<span id="line679"></span>    return !empty($analyzed_sql_results['querytype'])
<span id="line680"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> (($analyzed_sql_results['querytype'] == 'ALTER')
<span id="line681"></span>            || ($analyzed_sql_results['querytype'] == 'DROP'));
<span id="line682"></span>}
<span id="line683"></span>
<span id="line684"></span>/**
<span id="line685"></span> * Function to check whether the user has rights to drop the database
<span id="line686"></span> *
<span id="line687"></span> * @param array   $analyzed_sql_results  the analyzed query and other variables set
<span id="line688"></span> *                                       after analyzing the query
<span id="line689"></span> * @param boolean $allowUserDropDatabase whether the user is allowed to drop db
<span id="line690"></span> * @param boolean $is_superuser          whether this user is a superuser
<span id="line691"></span> *
<span id="line692"></span> * @return boolean
<span id="line693"></span> */
<span id="line694"></span>function PMA_hasNoRightsToDropDatabase($analyzed_sql_results,
<span id="line695"></span>    $allowUserDropDatabase, $is_superuser
<span id="line696"></span>) {
<span id="line697"></span>    return ! $allowUserDropDatabase
<span id="line698"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> isset($analyzed_sql_results['drop_database'])
<span id="line699"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> $analyzed_sql_results['drop_database']
<span id="line700"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> ! $is_superuser;
<span id="line701"></span>}
<span id="line702"></span>
<span id="line703"></span>/**
<span id="line704"></span> * Function to set a column property
<span id="line705"></span> *
<span id="line706"></span> * @param Table  $pmatable      Table instance
<span id="line707"></span> * @param string $request_index col_order|col_visib
<span id="line708"></span> *
<span id="line709"></span> * @return boolean $retval
<span id="line710"></span> */
<span id="line711"></span>function PMA_setColumnProperty($pmatable, $request_index)
<span id="line712"></span>{
<span id="line713"></span>    $property_value = explode(',', $_REQUEST[$request_index]);
<span id="line714"></span>    switch($request_index) {
<span id="line715"></span>    case 'col_order':
<span id="line716"></span>        $property_to_set = Table::PROP_COLUMN_ORDER;
<span id="line717"></span>        break;
<span id="line718"></span>    case 'col_visib':
<span id="line719"></span>        $property_to_set = Table::PROP_COLUMN_VISIB;
<span id="line720"></span>        break;
<span id="line721"></span>    default:
<span id="line722"></span>        $property_to_set = '';
<span id="line723"></span>    }
<span id="line724"></span>    $retval = $pmatable-&gt;setUiProp(
<span id="line725"></span>        $property_to_set,
<span id="line726"></span>        $property_value,
<span id="line727"></span>        $_REQUEST['table_create_time']
<span id="line728"></span>    );
<span id="line729"></span>    if (gettype($retval) != 'boolean') {
<span id="line730"></span>        $response = PMA\libraries\Response::getInstance();
<span id="line731"></span>        $response-&gt;setRequestStatus(false);
<span id="line732"></span>        $response-&gt;addJSON('message', $retval-&gt;getString());
<span id="line733"></span>        exit;
<span id="line734"></span>    }
<span id="line735"></span>
<span id="line736"></span>    return $retval;
<span id="line737"></span>}
<span id="line738"></span>
<span id="line739"></span>/**
<span id="line740"></span> * Function to check the request for setting the column order or visibility
<span id="line741"></span> *
<span id="line742"></span> * @param String $table the current table
<span id="line743"></span> * @param String $db    the current database
<span id="line744"></span> *
<span id="line745"></span> * @return void
<span id="line746"></span> */
<span id="line747"></span>function PMA_setColumnOrderOrVisibility($table, $db)
<span id="line748"></span>{
<span id="line749"></span>    $pmatable = new Table($table, $db);
<span id="line750"></span>    $retval = false;
<span id="line751"></span>
<span id="line752"></span>    // set column order
<span id="line753"></span>    if (isset($_REQUEST['col_order'])) {
<span id="line754"></span>        $retval = PMA_setColumnProperty($pmatable, 'col_order');
<span id="line755"></span>    }
<span id="line756"></span>
<span id="line757"></span>    // set column visibility
<span id="line758"></span>    if ($retval === true <span><span>&amp;</span></span><span><span>&amp;</span></span> isset($_REQUEST['col_visib'])) {
<span id="line759"></span>        $retval = PMA_setColumnProperty($pmatable, 'col_visib');
<span id="line760"></span>    }
<span id="line761"></span>
<span id="line762"></span>    $response = PMA\libraries\Response::getInstance();
<span id="line763"></span>    $response-&gt;setRequestStatus($retval == true);
<span id="line764"></span>    exit;
<span id="line765"></span>}
<span id="line766"></span>
<span id="line767"></span>/**
<span id="line768"></span> * Function to add a bookmark
<span id="line769"></span> *
<span id="line770"></span> * @param String $goto goto page URL
<span id="line771"></span> *
<span id="line772"></span> * @return void
<span id="line773"></span> */
<span id="line774"></span>function PMA_addBookmark($goto)
<span id="line775"></span>{
<span id="line776"></span>    $result = PMA_Bookmark_save(
<span id="line777"></span>        $_POST['bkm_fields'],
<span id="line778"></span>        (isset($_POST['bkm_all_users'])
<span id="line779"></span>            <span><span>&amp;</span></span><span><span>&amp;</span></span> $_POST['bkm_all_users'] == 'true' ? true : false
<span id="line780"></span>        )
<span id="line781"></span>    );
<span id="line782"></span>    $response = Response::getInstance();
<span id="line783"></span>    if ($response-&gt;isAjax()) {
<span id="line784"></span>        if ($result) {
<span id="line785"></span>            $msg = Message::success(__('Bookmark %s has been created.'));
<span id="line786"></span>            $msg-&gt;addParam($_POST['bkm_fields']['bkm_label']);
<span id="line787"></span>            $response-&gt;addJSON('message', $msg);
<span id="line788"></span>        } else {
<span id="line789"></span>            $msg = PMA\libraries\message::error(__('Bookmark not created!'));
<span id="line790"></span>            $response-&gt;setRequestStatus(false);
<span id="line791"></span>            $response-&gt;addJSON('message', $msg);
<span id="line792"></span>        }
<span id="line793"></span>        exit;
<span id="line794"></span>    } else {
<span id="line795"></span>        // go back to sql.php to redisplay query; do not use <span class="entity"><span>&amp;</span>amp;</span> in this case:
<span id="line796"></span>        /**
<span id="line797"></span>         * @todo In which scenario does this happen?
<span id="line798"></span>         */
<span id="line799"></span>        PMA_sendHeaderLocation(
<span id="line800"></span>            './' . $goto
<span id="line801"></span>            . '<span><span class="error" title="“&amp;” did not start a character reference. (“&amp;” probably should have been escaped as “&amp;amp;”.)">&amp;</span>la</span>bel=' . $_POST['bkm_fields']['bkm_label']
<span id="line802"></span>        );
<span id="line803"></span>    }
<span id="line804"></span>}
<span id="line805"></span>
<span id="line806"></span>/**
<span id="line807"></span> * Function to find the real end of rows
<span id="line808"></span> *
<span id="line809"></span> * @param String $db    the current database
<span id="line810"></span> * @param String $table the current table
<span id="line811"></span> *
<span id="line812"></span> * @return mixed the number of rows if "retain" param is true, otherwise true
<span id="line813"></span> */
<span id="line814"></span>function PMA_findRealEndOfRows($db, $table)
<span id="line815"></span>{
<span id="line816"></span>    $unlim_num_rows = $GLOBALS['dbi']-&gt;getTable($db, $table)-&gt;countRecords(true);
<span id="line817"></span>    $_SESSION['tmpval']['pos'] = PMA_getStartPosToDisplayRow($unlim_num_rows);
<span id="line818"></span>
<span id="line819"></span>    return $unlim_num_rows;
<span id="line820"></span>}
<span id="line821"></span>
<span id="line822"></span>/**
<span id="line823"></span> * Function to get values for the relational columns
<span id="line824"></span> *
<span id="line825"></span> * @param String $db    the current database
<span id="line826"></span> * @param String $table the current table
<span id="line827"></span> *
<span id="line828"></span> * @return void
<span id="line829"></span> */
<span id="line830"></span>function PMA_getRelationalValues($db, $table)
<span id="line831"></span>{
<span id="line832"></span>    $column = $_REQUEST['column'];
<span id="line833"></span>    if ($_SESSION['tmpval']['relational_display'] == 'D'
<span id="line834"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> isset($_REQUEST['relation_key_or_display_column'])
<span id="line835"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> $_REQUEST['relation_key_or_display_column']
<span id="line836"></span>    ) {
<span id="line837"></span>        $curr_value = $_REQUEST['relation_key_or_display_column'];
<span id="line838"></span>    } else {
<span id="line839"></span>        $curr_value = $_REQUEST['curr_value'];
<span id="line840"></span>    }
<span id="line841"></span>    $dropdown = PMA_getHtmlForRelationalColumnDropdown(
<span id="line842"></span>        $db, $table, $column, $curr_value
<span id="line843"></span>    );
<span id="line844"></span>    $response = PMA\libraries\Response::getInstance();
<span id="line845"></span>    $response-&gt;addJSON('dropdown', $dropdown);
<span id="line846"></span>    exit;
<span id="line847"></span>}
<span id="line848"></span>
<span id="line849"></span>/**
<span id="line850"></span> * Function to get values for Enum or Set Columns
<span id="line851"></span> *
<span id="line852"></span> * @param String $db         the current database
<span id="line853"></span> * @param String $table      the current table
<span id="line854"></span> * @param String $columnType whether enum or set
<span id="line855"></span> *
<span id="line856"></span> * @return void
<span id="line857"></span> */
<span id="line858"></span>function PMA_getEnumOrSetValues($db, $table, $columnType)
<span id="line859"></span>{
<span id="line860"></span>    $column = $_REQUEST['column'];
<span id="line861"></span>    $curr_value = $_REQUEST['curr_value'];
<span id="line862"></span>    $response = PMA\libraries\Response::getInstance();
<span id="line863"></span>    if ($columnType == "enum") {
<span id="line864"></span>        $dropdown = PMA_getHtmlForEnumColumnDropdown(
<span id="line865"></span>            $db, $table, $column, $curr_value
<span id="line866"></span>        );
<span id="line867"></span>        $response-&gt;addJSON('dropdown', $dropdown);
<span id="line868"></span>    } else {
<span id="line869"></span>        $select = PMA_getHtmlForSetColumn(
<span id="line870"></span>            $db, $table, $column, $curr_value
<span id="line871"></span>        );
<span id="line872"></span>        $response-&gt;addJSON('select', $select);
<span id="line873"></span>    }
<span id="line874"></span>    exit;
<span id="line875"></span>}
<span id="line876"></span>
<span id="line877"></span>/**
<span id="line878"></span> * Function to get the default sql query for browsing page
<span id="line879"></span> *
<span id="line880"></span> * @param String $db    the current database
<span id="line881"></span> * @param String $table the current table
<span id="line882"></span> *
<span id="line883"></span> * @return String $sql_query the default $sql_query for browse page
<span id="line884"></span> */
<span id="line885"></span>function PMA_getDefaultSqlQueryForBrowse($db, $table)
<span id="line886"></span>{
<span id="line887"></span>    include_once 'libraries/bookmark.lib.php';
<span id="line888"></span>    $book_sql_query = PMA_Bookmark_get(
<span id="line889"></span>        $db,
<span id="line890"></span>        '\'' . $GLOBALS['dbi']-&gt;escapeString($table) . '\'',
<span id="line891"></span>        'label',
<span id="line892"></span>        false,
<span id="line893"></span>        true
<span id="line894"></span>    );
<span id="line895"></span>
<span id="line896"></span>    if (! empty($book_sql_query)) {
<span id="line897"></span>        $GLOBALS['using_bookmark_message'] = Message::notice(
<span id="line898"></span>            __('Using bookmark "%s" as default browse query.')
<span id="line899"></span>        );
<span id="line900"></span>        $GLOBALS['using_bookmark_message']-&gt;addParam($table);
<span id="line901"></span>        $GLOBALS['using_bookmark_message']-&gt;addMessage(
<span id="line902"></span>            PMA\libraries\Util::showDocu('faq', 'faq6-22')
<span id="line903"></span>        );
<span id="line904"></span>        $sql_query = $book_sql_query;
<span id="line905"></span>    } else {
<span id="line906"></span>
<span id="line907"></span>        $defaultOrderByClause = '';
<span id="line908"></span>
<span id="line909"></span>        if (isset($GLOBALS['cfg']['TablePrimaryKeyOrder'])
<span id="line910"></span>            <span><span>&amp;</span></span><span><span>&amp;</span></span> ($GLOBALS['cfg']['TablePrimaryKeyOrder'] !== 'NONE')
<span id="line911"></span>        ) {
<span id="line912"></span>
<span id="line913"></span>            $primaryKey     = null;
<span id="line914"></span>            $primary        = PMA\libraries\Index::getPrimary($table, $db);
<span id="line915"></span>
<span id="line916"></span>            if ($primary !== false) {
<span id="line917"></span>
<span id="line918"></span>                $primarycols    = $primary-&gt;getColumns();
<span id="line919"></span>
<span id="line920"></span>                foreach ($primarycols as $col) {
<span id="line921"></span>                    $primaryKey = $col-&gt;getName();
<span id="line922"></span>                    break;
<span id="line923"></span>                }
<span id="line924"></span>
<span id="line925"></span>                if ($primaryKey != null) {
<span id="line926"></span>                    $defaultOrderByClause = ' ORDER BY '
<span id="line927"></span>                        . PMA\libraries\Util::backquote($table) . '.'
<span id="line928"></span>                        . PMA\libraries\Util::backquote($primaryKey) . ' '
<span id="line929"></span>                        . $GLOBALS['cfg']['TablePrimaryKeyOrder'];
<span id="line930"></span>                }
<span id="line931"></span>
<span id="line932"></span>            }
<span id="line933"></span>
<span id="line934"></span>        }
<span id="line935"></span>
<span id="line936"></span>        $sql_query = 'SELECT * FROM ' . PMA\libraries\Util::backquote($table)
<span id="line937"></span>            . $defaultOrderByClause;
<span id="line938"></span>
<span id="line939"></span>    }
<span id="line940"></span>    unset($book_sql_query);
<span id="line941"></span>
<span id="line942"></span>    return $sql_query;
<span id="line943"></span>}
<span id="line944"></span>
<span id="line945"></span>/**
<span id="line946"></span> * Responds an error when an error happens when executing the query
<span id="line947"></span> *
<span id="line948"></span> * @param boolean $is_gotofile    whether goto file or not
<span id="line949"></span> * @param String  $error          error after executing the query
<span id="line950"></span> * @param String  $full_sql_query full sql query
<span id="line951"></span> *
<span id="line952"></span> * @return void
<span id="line953"></span> */
<span id="line954"></span>function PMA_handleQueryExecuteError($is_gotofile, $error, $full_sql_query)
<span id="line955"></span>{
<span id="line956"></span>    if ($is_gotofile) {
<span id="line957"></span>        $message = PMA\libraries\Message::rawError($error);
<span id="line958"></span>        $response = PMA\libraries\Response::getInstance();
<span id="line959"></span>        $response-&gt;setRequestStatus(false);
<span id="line960"></span>        $response-&gt;addJSON('message', $message);
<span id="line961"></span>    } else {
<span id="line962"></span>        PMA\libraries\Util::mysqlDie($error, $full_sql_query, '', '');
<span id="line963"></span>    }
<span id="line964"></span>    exit;
<span id="line965"></span>}
<span id="line966"></span>
<span id="line967"></span>/**
<span id="line968"></span> * Function to store the query as a bookmark
<span id="line969"></span> *
<span id="line970"></span> * @param String  $db                     the current database
<span id="line971"></span> * @param String  $bkm_user               the bookmarking user
<span id="line972"></span> * @param String  $sql_query_for_bookmark the query to be stored in bookmark
<span id="line973"></span> * @param String  $bkm_label              bookmark label
<span id="line974"></span> * @param boolean $bkm_replace            whether to replace existing bookmarks
<span id="line975"></span> *
<span id="line976"></span> * @return void
<span id="line977"></span> */
<span id="line978"></span>function PMA_storeTheQueryAsBookmark($db, $bkm_user, $sql_query_for_bookmark,
<span id="line979"></span>    $bkm_label, $bkm_replace
<span id="line980"></span>) {
<span id="line981"></span>    include_once 'libraries/bookmark.lib.php';
<span id="line982"></span>    $bfields = array(
<span id="line983"></span>                 'bkm_database' =&gt; $db,
<span id="line984"></span>                 'bkm_user'  =&gt; $bkm_user,
<span id="line985"></span>                 'bkm_sql_query' =&gt; $sql_query_for_bookmark,
<span id="line986"></span>                 'bkm_label' =&gt; $bkm_label
<span id="line987"></span>    );
<span id="line988"></span>
<span id="line989"></span>    // Should we replace bookmark?
<span id="line990"></span>    if (isset($bkm_replace)) {
<span id="line991"></span>        $bookmarks = PMA_Bookmark_getList($db);
<span id="line992"></span>        foreach ($bookmarks as $key =&gt; $val) {
<span id="line993"></span>            if ($val['label'] == $bkm_label) {
<span id="line994"></span>                PMA_Bookmark_delete($key);
<span id="line995"></span>            }
<span id="line996"></span>        }
<span id="line997"></span>    }
<span id="line998"></span>
<span id="line999"></span>    PMA_Bookmark_save($bfields, isset($_POST['bkm_all_users']));
<span id="line1000"></span>
<span id="line1001"></span>}
<span id="line1002"></span>
<span id="line1003"></span>/**
<span id="line1004"></span> * Executes the SQL query and measures its execution time
<span id="line1005"></span> *
<span id="line1006"></span> * @param String $full_sql_query the full sql query
<span id="line1007"></span> *
<span id="line1008"></span> * @return array ($result, $querytime)
<span id="line1009"></span> */
<span id="line1010"></span>function PMA_executeQueryAndMeasureTime($full_sql_query)
<span id="line1011"></span>{
<span id="line1012"></span>    // close session in case the query takes too long
<span id="line1013"></span>    session_write_close();
<span id="line1014"></span>
<span id="line1015"></span>    // Measure query time.
<span id="line1016"></span>    $querytime_before = array_sum(explode(' ', microtime()));
<span id="line1017"></span>
<span id="line1018"></span>    $result = @$GLOBALS['dbi']-&gt;tryQuery(
<span id="line1019"></span>        $full_sql_query, null, PMA\libraries\DatabaseInterface::QUERY_STORE
<span id="line1020"></span>    );
<span id="line1021"></span>    $querytime_after = array_sum(explode(' ', microtime()));
<span id="line1022"></span>
<span id="line1023"></span>    // reopen session
<span id="line1024"></span>    session_start();
<span id="line1025"></span>
<span id="line1026"></span>    return array($result, $querytime_after - $querytime_before);
<span id="line1027"></span>}
<span id="line1028"></span>
<span id="line1029"></span>/**
<span id="line1030"></span> * Function to get the affected or changed number of rows after executing a query
<span id="line1031"></span> *
<span id="line1032"></span> * @param boolean $is_affected whether the query affected a table
<span id="line1033"></span> * @param mixed   $result      results of executing the query
<span id="line1034"></span> *
<span id="line1035"></span> * @return int    $num_rows    number of rows affected or changed
<span id="line1036"></span> */
<span id="line1037"></span>function PMA_getNumberOfRowsAffectedOrChanged($is_affected, $result)
<span id="line1038"></span>{
<span id="line1039"></span>    if (! $is_affected) {
<span id="line1040"></span>        $num_rows = ($result) ? @$GLOBALS['dbi']-&gt;numRows($result) : 0;
<span id="line1041"></span>    } else {
<span id="line1042"></span>        $num_rows = @$GLOBALS['dbi']-&gt;affectedRows();
<span id="line1043"></span>    }
<span id="line1044"></span>
<span id="line1045"></span>    return $num_rows;
<span id="line1046"></span>}
<span id="line1047"></span>
<span id="line1048"></span>/**
<span id="line1049"></span> * Checks if the current database has changed
<span id="line1050"></span> * This could happen if the user sends a query like "USE `database`;"
<span id="line1051"></span> *
<span id="line1052"></span> * @param String $db the database in the query
<span id="line1053"></span> *
<span id="line1054"></span> * @return int $reload whether to reload the navigation(1) or not(0)
<span id="line1055"></span> */
<span id="line1056"></span>function PMA_hasCurrentDbChanged($db)
<span id="line1057"></span>{
<span id="line1058"></span>    if (mb_strlen($db)) {
<span id="line1059"></span>        $current_db = $GLOBALS['dbi']-&gt;fetchValue('SELECT DATABASE()');
<span id="line1060"></span>        // $current_db is false, except when a USE statement was sent
<span id="line1061"></span>        return ($current_db != false) <span><span>&amp;</span></span><span><span>&amp;</span></span> ($db !== $current_db);
<span id="line1062"></span>    }
<span id="line1063"></span>
<span id="line1064"></span>    return false;
<span id="line1065"></span>}
<span id="line1066"></span>
<span id="line1067"></span>/**
<span id="line1068"></span> * If a table, database or column gets dropped, clean comments.
<span id="line1069"></span> *
<span id="line1070"></span> * @param String $db     current database
<span id="line1071"></span> * @param String $table  current table
<span id="line1072"></span> * @param String $column current column
<span id="line1073"></span> * @param bool   $purge  whether purge set or not
<span id="line1074"></span> *
<span id="line1075"></span> * @return array $extra_data
<span id="line1076"></span> */
<span id="line1077"></span>function PMA_cleanupRelations($db, $table, $column, $purge)
<span id="line1078"></span>{
<span id="line1079"></span>    include_once 'libraries/relation_cleanup.lib.php';
<span id="line1080"></span>
<span id="line1081"></span>    if (! empty($purge) <span><span>&amp;</span></span><span><span>&amp;</span></span> mb_strlen($db)) {
<span id="line1082"></span>        if (mb_strlen($table)) {
<span id="line1083"></span>            if (isset($column) <span><span>&amp;</span></span><span><span>&amp;</span></span> mb_strlen($column)) {
<span id="line1084"></span>                PMA_relationsCleanupColumn($db, $table, $column);
<span id="line1085"></span>            } else {
<span id="line1086"></span>                PMA_relationsCleanupTable($db, $table);
<span id="line1087"></span>            }
<span id="line1088"></span>        } else {
<span id="line1089"></span>            PMA_relationsCleanupDatabase($db);
<span id="line1090"></span>        }
<span id="line1091"></span>    }
<span id="line1092"></span>}
<span id="line1093"></span>
<span id="line1094"></span>/**
<span id="line1095"></span> * Function to count the total number of rows for the same 'SELECT' query without
<span id="line1096"></span> * the 'LIMIT' clause that may have been programatically added
<span id="line1097"></span> *
<span id="line1098"></span> * @param int    $num_rows             number of rows affected/changed by the query
<span id="line1099"></span> * @param bool   $justBrowsing         whether just browsing or not
<span id="line1100"></span> * @param string $db                   the current database
<span id="line1101"></span> * @param string $table                the current table
<span id="line1102"></span> * @param array  $analyzed_sql_results the analyzed query and other variables set
<span id="line1103"></span> *                                     after analyzing the query
<span id="line1104"></span> *
<span id="line1105"></span> * @return int $unlim_num_rows unlimited number of rows
<span id="line1106"></span> */
<span id="line1107"></span>function PMA_countQueryResults(
<span id="line1108"></span>    $num_rows, $justBrowsing, $db, $table, $analyzed_sql_results
<span id="line1109"></span>) {
<span id="line1110"></span>
<span id="line1111"></span>    /* Shortcut for not analyzed/empty query */
<span id="line1112"></span>    if (empty($analyzed_sql_results)) {
<span id="line1113"></span>        return 0;
<span id="line1114"></span>    }
<span id="line1115"></span>
<span id="line1116"></span>    if (!PMA_isAppendLimitClause($analyzed_sql_results)) {
<span id="line1117"></span>        // if we did not append a limit, set this to get a correct
<span id="line1118"></span>        // "Showing rows..." message
<span id="line1119"></span>        // $_SESSION['tmpval']['max_rows'] = 'all';
<span id="line1120"></span>        $unlim_num_rows = $num_rows;
<span id="line1121"></span>    } elseif ($analyzed_sql_results['querytype'] == 'SELECT'
<span id="line1122"></span>        || $analyzed_sql_results['is_subquery']
<span id="line1123"></span>    ) {
<span id="line1124"></span>        //    c o u n t    q u e r y
<span id="line1125"></span>
<span id="line1126"></span>        // If we are "just browsing", there is only one table (and no join),
<span id="line1127"></span>        // and no WHERE clause (or just 'WHERE 1 '),
<span id="line1128"></span>        // we do a quick count (which uses MaxExactCount) because
<span id="line1129"></span>        // SQL_CALC_FOUND_ROWS is not quick on large InnoDB tables
<span id="line1130"></span>
<span id="line1131"></span>        // However, do not count again if we did it previously
<span id="line1132"></span>        // due to $find_real_end == true
<span id="line1133"></span>        if ($justBrowsing) {
<span id="line1134"></span>            // Get row count (is approximate for InnoDB)
<span id="line1135"></span>            $unlim_num_rows = $GLOBALS['dbi']-&gt;getTable($db, $table)-&gt;countRecords();
<span id="line1136"></span>            /**
<span id="line1137"></span>             * @todo Can we know at this point that this is InnoDB,
<span id="line1138"></span>             *       (in this case there would be no need for getting
<span id="line1139"></span>             *       an exact count)?
<span id="line1140"></span>             */
<span id="line1141"></span>            if ($unlim_num_rows </span><span class="error" title="Bad character after “&lt;”. Probable cause: Unescaped “&lt;”. Try escaping it as “&amp;lt;”.">&lt; </span><span>$GLOBALS['cfg']['MaxExactCount']) {
<span id="line1142"></span>                // Get the exact count if approximate count
<span id="line1143"></span>                // is less than MaxExactCount
<span id="line1144"></span>                /**
<span id="line1145"></span>                 * @todo In countRecords(), MaxExactCount is also verified,
<span id="line1146"></span>                 *       so can we avoid checking it twice?
<span id="line1147"></span>                 */
<span id="line1148"></span>                $unlim_num_rows = $GLOBALS['dbi']-&gt;getTable($db, $table)
<span id="line1149"></span>                    -&gt;countRecords(true);
<span id="line1150"></span>            }
<span id="line1151"></span>
<span id="line1152"></span>        } else {
<span id="line1153"></span>
<span id="line1154"></span>            // The SQL_CALC_FOUND_ROWS option of the SELECT statement is used.
<span id="line1155"></span>
<span id="line1156"></span>            // For UNION statements, only a SQL_CALC_FOUND_ROWS is required
<span id="line1157"></span>            // after the first SELECT.
<span id="line1158"></span>
<span id="line1159"></span>            $count_query = SqlParser\Utils\Query::replaceClause(
<span id="line1160"></span>                $analyzed_sql_results['statement'],
<span id="line1161"></span>                $analyzed_sql_results['parser']-&gt;list,
<span id="line1162"></span>                'SELECT SQL_CALC_FOUND_ROWS',
<span id="line1163"></span>                null,
<span id="line1164"></span>                true
<span id="line1165"></span>            );
<span id="line1166"></span>
<span id="line1167"></span>            // Another LIMIT clause is added to avoid long delays.
<span id="line1168"></span>            // A complete result will be returned anyway, but the LIMIT would
<span id="line1169"></span>            // stop the query as soon as the result that is required has been
<span id="line1170"></span>            // computed.
<span id="line1171"></span>
<span id="line1172"></span>            if (empty($analyzed_sql_results['union'])) {
<span id="line1173"></span>                $count_query .= ' LIMIT 1';
<span id="line1174"></span>            }
<span id="line1175"></span>
<span id="line1176"></span>            // Running the count query.
<span id="line1177"></span>            $GLOBALS['dbi']-&gt;tryQuery($count_query);
<span id="line1178"></span>
<span id="line1179"></span>            $unlim_num_rows = $GLOBALS['dbi']-&gt;fetchValue('SELECT FOUND_ROWS()');
<span id="line1180"></span>        } // end else "just browsing"
<span id="line1181"></span>    } else {// not $is_select
<span id="line1182"></span>        $unlim_num_rows = 0;
<span id="line1183"></span>    }
<span id="line1184"></span>
<span id="line1185"></span>    return $unlim_num_rows;
<span id="line1186"></span>}
<span id="line1187"></span>
<span id="line1188"></span>/**
<span id="line1189"></span> * Function to handle all aspects relating to executing the query
<span id="line1190"></span> *
<span id="line1191"></span> * @param array   $analyzed_sql_results   analyzed sql results
<span id="line1192"></span> * @param String  $full_sql_query         full sql query
<span id="line1193"></span> * @param boolean $is_gotofile            whether to go to a file
<span id="line1194"></span> * @param String  $db                     current database
<span id="line1195"></span> * @param String  $table                  current table
<span id="line1196"></span> * @param boolean $find_real_end          whether to find the real end
<span id="line1197"></span> * @param String  $sql_query_for_bookmark sql query to be stored as bookmark
<span id="line1198"></span> * @param array   $extra_data             extra data
<span id="line1199"></span> *
<span id="line1200"></span> * @return mixed
<span id="line1201"></span> */
<span id="line1202"></span>function PMA_executeTheQuery($analyzed_sql_results, $full_sql_query, $is_gotofile,
<span id="line1203"></span>    $db, $table, $find_real_end, $sql_query_for_bookmark, $extra_data
<span id="line1204"></span>) {
<span id="line1205"></span>    $response = PMA\libraries\Response::getInstance();
<span id="line1206"></span>    $response-&gt;getHeader()-&gt;getMenu()-&gt;setTable($table);
<span id="line1207"></span>
<span id="line1208"></span>    // Only if we ask to see the php code
<span id="line1209"></span>    if (isset($GLOBALS['show_as_php'])) {
<span id="line1210"></span>        $result = null;
<span id="line1211"></span>        $num_rows = 0;
<span id="line1212"></span>        $unlim_num_rows = 0;
<span id="line1213"></span>    } else { // If we don't ask to see the php code
<span id="line1214"></span>        if (isset($_SESSION['profiling'])
<span id="line1215"></span>            <span><span>&amp;</span></span><span><span>&amp;</span></span> PMA\libraries\Util::profilingSupported()
<span id="line1216"></span>        ) {
<span id="line1217"></span>            $GLOBALS['dbi']-&gt;query('SET PROFILING=1;');
<span id="line1218"></span>        }
<span id="line1219"></span>
<span id="line1220"></span>        list(
<span id="line1221"></span>            $result,
<span id="line1222"></span>            $GLOBALS['querytime']
<span id="line1223"></span>        ) = PMA_executeQueryAndMeasureTime($full_sql_query);
<span id="line1224"></span>
<span id="line1225"></span>        // Displays an error message if required and stop parsing the script
<span id="line1226"></span>        $error = $GLOBALS['dbi']-&gt;getError();
<span id="line1227"></span>        if ($error <span><span>&amp;</span></span><span><span>&amp;</span></span> $GLOBALS['cfg']['IgnoreMultiSubmitErrors']) {
<span id="line1228"></span>            $extra_data['error'] = $error;
<span id="line1229"></span>        } elseif ($error) {
<span id="line1230"></span>            PMA_handleQueryExecuteError($is_gotofile, $error, $full_sql_query);
<span id="line1231"></span>        }
<span id="line1232"></span>
<span id="line1233"></span>        // If there are no errors and bookmarklabel was given,
<span id="line1234"></span>        // store the query as a bookmark
<span id="line1235"></span>        if (! empty($_POST['bkm_label']) <span><span>&amp;</span></span><span><span>&amp;</span></span> ! empty($sql_query_for_bookmark)) {
<span id="line1236"></span>            $cfgBookmark = PMA_Bookmark_getParams();
<span id="line1237"></span>            PMA_storeTheQueryAsBookmark(
<span id="line1238"></span>                $db, $cfgBookmark['user'],
<span id="line1239"></span>                $sql_query_for_bookmark, $_POST['bkm_label'],
<span id="line1240"></span>                isset($_POST['bkm_replace']) ? $_POST['bkm_replace'] : null
<span id="line1241"></span>            );
<span id="line1242"></span>        } // end store bookmarks
<span id="line1243"></span>
<span id="line1244"></span>        // Gets the number of rows affected/returned
<span id="line1245"></span>        // (This must be done immediately after the query because
<span id="line1246"></span>        // mysql_affected_rows() reports about the last query done)
<span id="line1247"></span>        $num_rows = PMA_getNumberOfRowsAffectedOrChanged(
<span id="line1248"></span>            $analyzed_sql_results['is_affected'], $result
<span id="line1249"></span>        );
<span id="line1250"></span>
<span id="line1251"></span>        // Grabs the profiling results
<span id="line1252"></span>        if (isset($_SESSION['profiling'])
<span id="line1253"></span>            <span><span>&amp;</span></span><span><span>&amp;</span></span> PMA\libraries\Util::profilingSupported()
<span id="line1254"></span>        ) {
<span id="line1255"></span>            $profiling_results = $GLOBALS['dbi']-&gt;fetchResult('SHOW PROFILE;');
<span id="line1256"></span>        }
<span id="line1257"></span>
<span id="line1258"></span>        $justBrowsing = PMA_isJustBrowsing(
<span id="line1259"></span>            $analyzed_sql_results, isset($find_real_end) ? $find_real_end : null
<span id="line1260"></span>        );
<span id="line1261"></span>
<span id="line1262"></span>        $unlim_num_rows = PMA_countQueryResults(
<span id="line1263"></span>            $num_rows, $justBrowsing, $db, $table, $analyzed_sql_results
<span id="line1264"></span>        );
<span id="line1265"></span>
<span id="line1266"></span>        PMA_cleanupRelations(
<span id="line1267"></span>            isset($db) ? $db : '',
<span id="line1268"></span>            isset($table) ? $table : '',
<span id="line1269"></span>            isset($_REQUEST['dropped_column']) ? $_REQUEST['dropped_column'] : null,
<span id="line1270"></span>            isset($_REQUEST['purge']) ? $_REQUEST['purge'] : null
<span id="line1271"></span>        );
<span id="line1272"></span>
<span id="line1273"></span>        if (isset($_REQUEST['dropped_column'])
<span id="line1274"></span>            <span><span>&amp;</span></span><span><span>&amp;</span></span> mb_strlen($db)
<span id="line1275"></span>            <span><span>&amp;</span></span><span><span>&amp;</span></span> mb_strlen($table)
<span id="line1276"></span>        ) {
<span id="line1277"></span>            // to refresh the list of indexes (Ajax mode)
<span id="line1278"></span>            $extra_data['indexes_list'] = PMA\libraries\Index::getHtmlForIndexes(
<span id="line1279"></span>                $table,
<span id="line1280"></span>                $db
<span id="line1281"></span>            );
<span id="line1282"></span>        }
<span id="line1283"></span>    }
<span id="line1284"></span>
<span id="line1285"></span>    return array($result, $num_rows, $unlim_num_rows,
<span id="line1286"></span>        isset($profiling_results) ? $profiling_results : null, $extra_data
<span id="line1287"></span>    );
<span id="line1288"></span>}
<span id="line1289"></span>/**
<span id="line1290"></span> * Delete related tranformation information
<span id="line1291"></span> *
<span id="line1292"></span> * @param String $db                   current database
<span id="line1293"></span> * @param String $table                current table
<span id="line1294"></span> * @param array  $analyzed_sql_results analyzed sql results
<span id="line1295"></span> *
<span id="line1296"></span> * @return void
<span id="line1297"></span> */
<span id="line1298"></span>function PMA_deleteTransformationInfo($db, $table, $analyzed_sql_results)
<span id="line1299"></span>{
<span id="line1300"></span>    include_once 'libraries/transformations.lib.php';
<span id="line1301"></span>    $statement = $analyzed_sql_results['statement'];
<span id="line1302"></span>    if ($statement instanceof SqlParser\Statements\AlterStatement) {
<span id="line1303"></span>        if (!empty($statement-&gt;altered[0])
<span id="line1304"></span>            <span><span>&amp;</span></span><span><span>&amp;</span></span> $statement-&gt;altered[0]-&gt;options-&gt;has('DROP')
<span id="line1305"></span>        ) {
<span id="line1306"></span>            if (!empty($statement-&gt;altered[0]-&gt;field-&gt;column)) {
<span id="line1307"></span>                PMA_clearTransformations(
<span id="line1308"></span>                    $db,
<span id="line1309"></span>                    $table,
<span id="line1310"></span>                    $statement-&gt;altered[0]-&gt;field-&gt;column
<span id="line1311"></span>                );
<span id="line1312"></span>            }
<span id="line1313"></span>        }
<span id="line1314"></span>    } elseif ($statement instanceof SqlParser\Statements\DropStatement) {
<span id="line1315"></span>        PMA_clearTransformations($db, $table);
<span id="line1316"></span>    }
<span id="line1317"></span>}
<span id="line1318"></span>
<span id="line1319"></span>/**
<span id="line1320"></span> * Function to get the message for the no rows returned case
<span id="line1321"></span> *
<span id="line1322"></span> * @param string $message_to_show      message to show
<span id="line1323"></span> * @param array  $analyzed_sql_results analyzed sql results
<span id="line1324"></span> * @param int    $num_rows             number of rows
<span id="line1325"></span> *
<span id="line1326"></span> * @return string $message
<span id="line1327"></span> */
<span id="line1328"></span>function PMA_getMessageForNoRowsReturned($message_to_show,
<span id="line1329"></span>    $analyzed_sql_results, $num_rows
<span id="line1330"></span>) {
<span id="line1331"></span>    if ($analyzed_sql_results['querytype'] == 'DELETE"') {
<span id="line1332"></span>        $message = Message::getMessageForDeletedRows($num_rows);
<span id="line1333"></span>    } elseif ($analyzed_sql_results['is_insert']) {
<span id="line1334"></span>        if ($analyzed_sql_results['querytype'] == 'REPLACE') {
<span id="line1335"></span>            // For REPLACE we get DELETED + INSERTED row count,
<span id="line1336"></span>            // so we have to call it affected
<span id="line1337"></span>            $message = Message::getMessageForAffectedRows($num_rows);
<span id="line1338"></span>        } else {
<span id="line1339"></span>            $message = Message::getMessageForInsertedRows($num_rows);
<span id="line1340"></span>        }
<span id="line1341"></span>        $insert_id = $GLOBALS['dbi']-&gt;insertId();
<span id="line1342"></span>        if ($insert_id != 0) {
<span id="line1343"></span>            // insert_id is id of FIRST record inserted in one insert,
<span id="line1344"></span>            // so if we inserted multiple rows, we had to increment this
<span id="line1345"></span>            $message-&gt;addMessage('[br]');
<span id="line1346"></span>            // need to use a temporary because the Message class
<span id="line1347"></span>            // currently supports adding parameters only to the first
<span id="line1348"></span>            // message
<span id="line1349"></span>            $_inserted = Message::notice(__('Inserted row id: %1$d'));
<span id="line1350"></span>            $_inserted-&gt;addParam($insert_id + $num_rows - 1);
<span id="line1351"></span>            $message-&gt;addMessage($_inserted);
<span id="line1352"></span>        }
<span id="line1353"></span>    } elseif ($analyzed_sql_results['is_affected']) {
<span id="line1354"></span>        $message = Message::getMessageForAffectedRows($num_rows);
<span id="line1355"></span>
<span id="line1356"></span>        // Ok, here is an explanation for the !$is_select.
<span id="line1357"></span>        // The form generated by sql_query_form.lib.php
<span id="line1358"></span>        // and db_sql.php has many submit buttons
<span id="line1359"></span>        // on the same form, and some confusion arises from the
<span id="line1360"></span>        // fact that $message_to_show is sent for every case.
<span id="line1361"></span>        // The $message_to_show containing a success message and sent with
<span id="line1362"></span>        // the form should not have priority over errors
<span id="line1363"></span>    } elseif (! empty($message_to_show)
<span id="line1364"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> $analyzed_sql_results['querytype'] != 'SELECT'
<span id="line1365"></span>    ) {
<span id="line1366"></span>        $message = Message::rawSuccess(htmlspecialchars($message_to_show));
<span id="line1367"></span>    } elseif (! empty($GLOBALS['show_as_php'])) {
<span id="line1368"></span>        $message = Message::success(__('Showing as PHP code'));
<span id="line1369"></span>    } elseif (isset($GLOBALS['show_as_php'])) {
<span id="line1370"></span>        /* User disable showing as PHP, query is only displayed */
<span id="line1371"></span>        $message = Message::notice(__('Showing SQL query'));
<span id="line1372"></span>    } else {
<span id="line1373"></span>        $message = Message::success(
<span id="line1374"></span>            __('MySQL returned an empty result set (i.e. zero rows).')
<span id="line1375"></span>        );
<span id="line1376"></span>    }
<span id="line1377"></span>
<span id="line1378"></span>    if (isset($GLOBALS['querytime'])) {
<span id="line1379"></span>        $_querytime = Message::notice(
<span id="line1380"></span>            '(' . __('Query took %01.4f seconds.') . ')'
<span id="line1381"></span>        );
<span id="line1382"></span>        $_querytime-&gt;addParam($GLOBALS['querytime']);
<span id="line1383"></span>        $message-&gt;addMessage($_querytime);
<span id="line1384"></span>    }
<span id="line1385"></span>
<span id="line1386"></span>    // In case of ROLLBACK, notify the user.
<span id="line1387"></span>    if (isset($_REQUEST['rollback_query'])) {
<span id="line1388"></span>        $message-&gt;addMessage(__('[ROLLBACK occurred.]'));
<span id="line1389"></span>    }
<span id="line1390"></span>
<span id="line1391"></span>    return $message;
<span id="line1392"></span>}
<span id="line1393"></span>
<span id="line1394"></span>/**
<span id="line1395"></span> * Function to respond back when the query returns zero rows
<span id="line1396"></span> * This method is called
<span id="line1397"></span> * 1-&gt; When browsing an empty table
<span id="line1398"></span> * 2-&gt; When executing a query on a non empty table which returns zero results
<span id="line1399"></span> * 3-&gt; When executing a query on an empty table
<span id="line1400"></span> * 4-&gt; When executing an INSERT, UPDATE, DELETE query from the SQL tab
<span id="line1401"></span> * 5-&gt; When deleting a row from BROWSE tab
<span id="line1402"></span> * 6-&gt; When searching using the SEARCH tab which returns zero results
<span id="line1403"></span> * 7-&gt; When changing the structure of the table except change operation
<span id="line1404"></span> *
<span id="line1405"></span> * @param array          $analyzed_sql_results analyzed sql results
<span id="line1406"></span> * @param string         $db                   current database
<span id="line1407"></span> * @param string         $table                current table
<span id="line1408"></span> * @param string         $message_to_show      message to show
<span id="line1409"></span> * @param int            $num_rows             number of rows
<span id="line1410"></span> * @param DisplayResults $displayResultsObject DisplayResult instance
<span id="line1411"></span> * @param array          $extra_data           extra data
<span id="line1412"></span> * @param string         $pmaThemeImage        uri of the theme image
<span id="line1413"></span> * @param object         $result               executed query results
<span id="line1414"></span> * @param string         $sql_query            sql query
<span id="line1415"></span> * @param string         $complete_query       complete sql query
<span id="line1416"></span> *
<span id="line1417"></span> * @return string html
<span id="line1418"></span> */
<span id="line1419"></span>function PMA_getQueryResponseForNoResultsReturned($analyzed_sql_results, $db,
<span id="line1420"></span>    $table, $message_to_show, $num_rows, $displayResultsObject, $extra_data,
<span id="line1421"></span>    $pmaThemeImage, $result, $sql_query, $complete_query
<span id="line1422"></span>) {
<span id="line1423"></span>    if (PMA_isDeleteTransformationInfo($analyzed_sql_results)) {
<span id="line1424"></span>        PMA_deleteTransformationInfo($db, $table, $analyzed_sql_results);
<span id="line1425"></span>    }
<span id="line1426"></span>
<span id="line1427"></span>    if (isset($extra_data['error'])) {
<span id="line1428"></span>        $message = PMA\libraries\Message::rawError($extra_data['error']);
<span id="line1429"></span>    } else {
<span id="line1430"></span>        $message = PMA_getMessageForNoRowsReturned(
<span id="line1431"></span>            isset($message_to_show) ? $message_to_show : null,
<span id="line1432"></span>            $analyzed_sql_results, $num_rows
<span id="line1433"></span>        );
<span id="line1434"></span>    }
<span id="line1435"></span>
<span id="line1436"></span>    $html_output = '';
<span id="line1437"></span>    $html_message = PMA\libraries\Util::getMessage(
<span id="line1438"></span>        $message, $GLOBALS['sql_query'], 'success'
<span id="line1439"></span>    );
<span id="line1440"></span>    $html_output .= $html_message;
<span id="line1441"></span>    if (!isset($GLOBALS['show_as_php'])) {
<span id="line1442"></span>
<span id="line1443"></span>        if (! empty($GLOBALS['reload'])) {
<span id="line1444"></span>            $extra_data['reload'] = 1;
<span id="line1445"></span>            $extra_data['db'] = $GLOBALS['db'];
<span id="line1446"></span>        }
<span id="line1447"></span>
<span id="line1448"></span>        // For ajax requests add message and sql_query as JSON
<span id="line1449"></span>        if (empty($_REQUEST['ajax_page_request'])) {
<span id="line1450"></span>            $extra_data['message'] = $message;
<span id="line1451"></span>            if ($GLOBALS['cfg']['ShowSQL']) {
<span id="line1452"></span>                $extra_data['sql_query'] = $html_message;
<span id="line1453"></span>            }
<span id="line1454"></span>        }
<span id="line1455"></span>
<span id="line1456"></span>        $response = PMA\libraries\Response::getInstance();
<span id="line1457"></span>        $response-&gt;addJSON(isset($extra_data) ? $extra_data : array());
<span id="line1458"></span>
<span id="line1459"></span>        if (!empty($analyzed_sql_results['is_select']) <span><span>&amp;</span></span><span><span>&amp;</span></span>
<span id="line1460"></span>                !isset($extra_data['error'])) {
<span id="line1461"></span>            $url_query = isset($url_query) ? $url_query : null;
<span id="line1462"></span>
<span id="line1463"></span>            $displayParts = array(
<span id="line1464"></span>                'edit_lnk' =&gt; null,
<span id="line1465"></span>                'del_lnk' =&gt; null,
<span id="line1466"></span>                'sort_lnk' =&gt; '1',
<span id="line1467"></span>                'nav_bar'  =&gt; '0',
<span id="line1468"></span>                'bkm_form' =&gt; '1',
<span id="line1469"></span>                'text_btn' =&gt; '1',
<span id="line1470"></span>                'pview_lnk' =&gt; '1'
<span id="line1471"></span>            );
<span id="line1472"></span>
<span id="line1473"></span>            $html_output .= PMA_getHtmlForSqlQueryResultsTable(
<span id="line1474"></span>                $displayResultsObject,
<span id="line1475"></span>                $pmaThemeImage, $url_query, $displayParts,
<span id="line1476"></span>                false, 0, $num_rows, true, $result,
<span id="line1477"></span>                $analyzed_sql_results, true
<span id="line1478"></span>            );
<span id="line1479"></span>
<span id="line1480"></span>            $html_output .= $displayResultsObject-&gt;getCreateViewQueryResultOp(
<span id="line1481"></span>                $analyzed_sql_results
<span id="line1482"></span>            );
<span id="line1483"></span>
<span id="line1484"></span>            $cfgBookmark = PMA_Bookmark_getParams();
<span id="line1485"></span>            if ($cfgBookmark) {
<span id="line1486"></span>                $html_output .= PMA_getHtmlForBookmark(
<span id="line1487"></span>                    $displayParts,
<span id="line1488"></span>                    $cfgBookmark,
<span id="line1489"></span>                    $sql_query, $db, $table,
<span id="line1490"></span>                    isset($complete_query) ? $complete_query : $sql_query,
<span id="line1491"></span>                    $cfgBookmark['user']
<span id="line1492"></span>                );
<span id="line1493"></span>            }
<span id="line1494"></span>        }
<span id="line1495"></span>    }
<span id="line1496"></span>
<span id="line1497"></span>    return $html_output;
<span id="line1498"></span>}
<span id="line1499"></span>
<span id="line1500"></span>/**
<span id="line1501"></span> * Function to send response for ajax grid edit
<span id="line1502"></span> *
<span id="line1503"></span> * @param object $result result of the executed query
<span id="line1504"></span> *
<span id="line1505"></span> * @return void
<span id="line1506"></span> */
<span id="line1507"></span>function PMA_sendResponseForGridEdit($result)
<span id="line1508"></span>{
<span id="line1509"></span>    $row = $GLOBALS['dbi']-&gt;fetchRow($result);
<span id="line1510"></span>    $field_flags = $GLOBALS['dbi']-&gt;fieldFlags($result, 0);
<span id="line1511"></span>    if (stristr($field_flags, PMA\libraries\DisplayResults::BINARY_FIELD)) {
<span id="line1512"></span>        $row[0] = bin2hex($row[0]);
<span id="line1513"></span>    }
<span id="line1514"></span>    $response = PMA\libraries\Response::getInstance();
<span id="line1515"></span>    $response-&gt;addJSON('value', $row[0]);
<span id="line1516"></span>    exit;
<span id="line1517"></span>}
<span id="line1518"></span>
<span id="line1519"></span>/**
<span id="line1520"></span> * Function to get html for the sql query results div
<span id="line1521"></span> *
<span id="line1522"></span> * @param string  $previous_update_query_html html for the previously executed query
<span id="line1523"></span> * @param string  $profiling_chart_html       html for profiling
<span id="line1524"></span> * @param Message $missing_unique_column_msg  message for the missing unique column
<span id="line1525"></span> * @param Message $bookmark_created_msg       message for bookmark creation
<span id="line1526"></span> * @param string  $table_html                 html for the table for displaying sql
<span id="line1527"></span> *                                            results
<span id="line1528"></span> * @param string  $indexes_problems_html      html for displaying errors in indexes
<span id="line1529"></span> * @param string  $bookmark_support_html      html for displaying bookmark form
<span id="line1530"></span> *
<span id="line1531"></span> * @return string $html_output
<span id="line1532"></span> */
<span id="line1533"></span>function PMA_getHtmlForSqlQueryResults($previous_update_query_html,
<span id="line1534"></span>    $profiling_chart_html, $missing_unique_column_msg, $bookmark_created_msg,
<span id="line1535"></span>    $table_html, $indexes_problems_html, $bookmark_support_html
<span id="line1536"></span>) {
<span id="line1537"></span>    //begin the sqlqueryresults div here. container div
<span id="line1538"></span>    $html_output = '</span><span>&lt;<span class="start-tag">div</span> <span class="attribute-name">class</span>="<a class="attribute-value">sqlqueryresults ajax</a>"&gt;</span><span>';
<span id="line1539"></span>    $html_output .= isset($previous_update_query_html)
<span id="line1540"></span>        ? $previous_update_query_html : '';
<span id="line1541"></span>    $html_output .= isset($profiling_chart_html) ? $profiling_chart_html : '';
<span id="line1542"></span>    $html_output .= isset($missing_unique_column_msg)
<span id="line1543"></span>        ? $missing_unique_column_msg-&gt;getDisplay() : '';
<span id="line1544"></span>    $html_output .= isset($bookmark_created_msg)
<span id="line1545"></span>        ? $bookmark_created_msg-&gt;getDisplay() : '';
<span id="line1546"></span>    $html_output .= $table_html;
<span id="line1547"></span>    $html_output .= isset($indexes_problems_html) ? $indexes_problems_html : '';
<span id="line1548"></span>    $html_output .= isset($bookmark_support_html) ? $bookmark_support_html : '';
<span id="line1549"></span>    $html_output .= '</span><span>&lt;/<span class="end-tag">div</span>&gt;</span><span>'; // end sqlqueryresults div
<span id="line1550"></span>
<span id="line1551"></span>    return $html_output;
<span id="line1552"></span>}
<span id="line1553"></span>
<span id="line1554"></span>/**
<span id="line1555"></span> * Returns a message for successful creation of a bookmark or null if a bookmark
<span id="line1556"></span> * was not created
<span id="line1557"></span> *
<span id="line1558"></span> * @return Message $bookmark_created_msg
<span id="line1559"></span> */
<span id="line1560"></span>function PMA_getBookmarkCreatedMessage()
<span id="line1561"></span>{
<span id="line1562"></span>    if (isset($_GET['label'])) {
<span id="line1563"></span>        $bookmark_created_msg = Message::success(
<span id="line1564"></span>            __('Bookmark %s has been created.')
<span id="line1565"></span>        );
<span id="line1566"></span>        $bookmark_created_msg-&gt;addParam($_GET['label']);
<span id="line1567"></span>    } else {
<span id="line1568"></span>        $bookmark_created_msg = null;
<span id="line1569"></span>    }
<span id="line1570"></span>
<span id="line1571"></span>    return $bookmark_created_msg;
<span id="line1572"></span>}
<span id="line1573"></span>
<span id="line1574"></span>/**
<span id="line1575"></span> * Function to get html for the sql query results table
<span id="line1576"></span> *
<span id="line1577"></span> * @param DisplayResults $displayResultsObject instance of DisplayResult
<span id="line1578"></span> * @param string         $pmaThemeImage        theme image uri
<span id="line1579"></span> * @param string         $url_query            url query
<span id="line1580"></span> * @param array          $displayParts         the parts to display
<span id="line1581"></span> * @param bool           $editable             whether the result table is
<span id="line1582"></span> *                                             editable or not
<span id="line1583"></span> * @param int            $unlim_num_rows       unlimited number of rows
<span id="line1584"></span> * @param int            $num_rows             number of rows
<span id="line1585"></span> * @param bool           $showtable            whether to show table or not
<span id="line1586"></span> * @param object         $result               result of the executed query
<span id="line1587"></span> * @param array          $analyzed_sql_results analyzed sql results
<span id="line1588"></span> * @param bool           $is_limited_display   Show only limited operations or not
<span id="line1589"></span> *
<span id="line1590"></span> * @return String
<span id="line1591"></span> */
<span id="line1592"></span>function PMA_getHtmlForSqlQueryResultsTable($displayResultsObject,
<span id="line1593"></span>    $pmaThemeImage, $url_query, $displayParts,
<span id="line1594"></span>    $editable, $unlim_num_rows, $num_rows, $showtable, $result,
<span id="line1595"></span>    $analyzed_sql_results, $is_limited_display = false
<span id="line1596"></span>) {
<span id="line1597"></span>    $printview = isset($_REQUEST['printview']) ? $_REQUEST['printview'] : null;
<span id="line1598"></span>    $table_html = '';
<span id="line1599"></span>    $browse_dist = ! empty($_REQUEST['is_browse_distinct']);
<span id="line1600"></span>
<span id="line1601"></span>    if ($analyzed_sql_results['is_procedure']) {
<span id="line1602"></span>
<span id="line1603"></span>        do {
<span id="line1604"></span>            if (! isset($result)) {
<span id="line1605"></span>                $result = $GLOBALS['dbi']-&gt;storeResult();
<span id="line1606"></span>            }
<span id="line1607"></span>            $num_rows = $GLOBALS['dbi']-&gt;numRows($result);
<span id="line1608"></span>
<span id="line1609"></span>            if ($result !== false <span><span>&amp;</span></span><span><span>&amp;</span></span> $num_rows &gt; 0) {
<span id="line1610"></span>
<span id="line1611"></span>                $fields_meta = $GLOBALS['dbi']-&gt;getFieldsMeta($result);
<span id="line1612"></span>                $fields_cnt  = count($fields_meta);
<span id="line1613"></span>
<span id="line1614"></span>                $displayResultsObject-&gt;setProperties(
<span id="line1615"></span>                    $num_rows,
<span id="line1616"></span>                    $fields_meta,
<span id="line1617"></span>                    $analyzed_sql_results['is_count'],
<span id="line1618"></span>                    $analyzed_sql_results['is_export'],
<span id="line1619"></span>                    $analyzed_sql_results['is_func'],
<span id="line1620"></span>                    $analyzed_sql_results['is_analyse'],
<span id="line1621"></span>                    $num_rows,
<span id="line1622"></span>                    $fields_cnt,
<span id="line1623"></span>                    $GLOBALS['querytime'],
<span id="line1624"></span>                    $pmaThemeImage,
<span id="line1625"></span>                    $GLOBALS['text_dir'],
<span id="line1626"></span>                    $analyzed_sql_results['is_maint'],
<span id="line1627"></span>                    $analyzed_sql_results['is_explain'],
<span id="line1628"></span>                    $analyzed_sql_results['is_show'],
<span id="line1629"></span>                    $showtable,
<span id="line1630"></span>                    $printview,
<span id="line1631"></span>                    $url_query,
<span id="line1632"></span>                    $editable,
<span id="line1633"></span>                    $browse_dist
<span id="line1634"></span>                );
<span id="line1635"></span>
<span id="line1636"></span>                $displayParts = array(
<span id="line1637"></span>                    'edit_lnk' =&gt; $displayResultsObject::NO_EDIT_OR_DELETE,
<span id="line1638"></span>                    'del_lnk' =&gt; $displayResultsObject::NO_EDIT_OR_DELETE,
<span id="line1639"></span>                    'sort_lnk' =&gt; '1',
<span id="line1640"></span>                    'nav_bar'  =&gt; '1',
<span id="line1641"></span>                    'bkm_form' =&gt; '1',
<span id="line1642"></span>                    'text_btn' =&gt; '1',
<span id="line1643"></span>                    'pview_lnk' =&gt; '1'
<span id="line1644"></span>                );
<span id="line1645"></span>
<span id="line1646"></span>                $table_html .= $displayResultsObject-&gt;getTable(
<span id="line1647"></span>                    $result,
<span id="line1648"></span>                    $displayParts,
<span id="line1649"></span>                    $analyzed_sql_results,
<span id="line1650"></span>                    $is_limited_display
<span id="line1651"></span>                );
<span id="line1652"></span>            }
<span id="line1653"></span>
<span id="line1654"></span>            $GLOBALS['dbi']-&gt;freeResult($result);
<span id="line1655"></span>            unset($result);
<span id="line1656"></span>
<span id="line1657"></span>        } while ($GLOBALS['dbi']-&gt;moreResults() <span><span>&amp;</span></span><span><span>&amp;</span></span> $GLOBALS['dbi']-&gt;nextResult());
<span id="line1658"></span>
<span id="line1659"></span>    } else {
<span id="line1660"></span>        if (isset($result) <span><span>&amp;</span></span><span><span>&amp;</span></span> $result) {
<span id="line1661"></span>            $fields_meta = $GLOBALS['dbi']-&gt;getFieldsMeta($result);
<span id="line1662"></span>            $fields_cnt  = count($fields_meta);
<span id="line1663"></span>        }
<span id="line1664"></span>        $_SESSION['is_multi_query'] = false;
<span id="line1665"></span>        $displayResultsObject-&gt;setProperties(
<span id="line1666"></span>            $unlim_num_rows,
<span id="line1667"></span>            $fields_meta,
<span id="line1668"></span>            $analyzed_sql_results['is_count'],
<span id="line1669"></span>            $analyzed_sql_results['is_export'],
<span id="line1670"></span>            $analyzed_sql_results['is_func'],
<span id="line1671"></span>            $analyzed_sql_results['is_analyse'],
<span id="line1672"></span>            $num_rows,
<span id="line1673"></span>            $fields_cnt, $GLOBALS['querytime'],
<span id="line1674"></span>            $pmaThemeImage, $GLOBALS['text_dir'],
<span id="line1675"></span>            $analyzed_sql_results['is_maint'],
<span id="line1676"></span>            $analyzed_sql_results['is_explain'],
<span id="line1677"></span>            $analyzed_sql_results['is_show'],
<span id="line1678"></span>            $showtable,
<span id="line1679"></span>            $printview,
<span id="line1680"></span>            $url_query,
<span id="line1681"></span>            $editable,
<span id="line1682"></span>            $browse_dist
<span id="line1683"></span>        );
<span id="line1684"></span>
<span id="line1685"></span>        $table_html .= $displayResultsObject-&gt;getTable(
<span id="line1686"></span>            $result,
<span id="line1687"></span>            $displayParts,
<span id="line1688"></span>            $analyzed_sql_results,
<span id="line1689"></span>            $is_limited_display
<span id="line1690"></span>        );
<span id="line1691"></span>        $GLOBALS['dbi']-&gt;freeResult($result);
<span id="line1692"></span>    }
<span id="line1693"></span>
<span id="line1694"></span>    return $table_html;
<span id="line1695"></span>}
<span id="line1696"></span>
<span id="line1697"></span>/**
<span id="line1698"></span> * Function to get html for the previous query if there is such. If not will return
<span id="line1699"></span> * null
<span id="line1700"></span> *
<span id="line1701"></span> * @param string $disp_query   display query
<span id="line1702"></span> * @param bool   $showSql      whether to show sql
<span id="line1703"></span> * @param array  $sql_data     sql data
<span id="line1704"></span> * @param string $disp_message display message
<span id="line1705"></span> *
<span id="line1706"></span> * @return string $previous_update_query_html
<span id="line1707"></span> */
<span id="line1708"></span>function PMA_getHtmlForPreviousUpdateQuery($disp_query, $showSql, $sql_data,
<span id="line1709"></span>    $disp_message
<span id="line1710"></span>) {
<span id="line1711"></span>    // previous update query (from tbl_replace)
<span id="line1712"></span>    if (isset($disp_query) <span><span>&amp;</span></span><span><span>&amp;</span></span> ($showSql == true) <span><span>&amp;</span></span><span><span>&amp;</span></span> empty($sql_data)) {
<span id="line1713"></span>        $previous_update_query_html = PMA\libraries\Util::getMessage(
<span id="line1714"></span>            $disp_message, $disp_query, 'success'
<span id="line1715"></span>        );
<span id="line1716"></span>    } else {
<span id="line1717"></span>        $previous_update_query_html = null;
<span id="line1718"></span>    }
<span id="line1719"></span>
<span id="line1720"></span>    return $previous_update_query_html;
<span id="line1721"></span>}
<span id="line1722"></span>
<span id="line1723"></span>/**
<span id="line1724"></span> * To get the message if a column index is missing. If not will return null
<span id="line1725"></span> *
<span id="line1726"></span> * @param string  $table      current table
<span id="line1727"></span> * @param string  $db         current database
<span id="line1728"></span> * @param boolean $editable   whether the results table can be editable or not
<span id="line1729"></span> * @param boolean $has_unique whether there is a unique key
<span id="line1730"></span> *
<span id="line1731"></span> * @return Message $message
<span id="line1732"></span> */
<span id="line1733"></span>function PMA_getMessageIfMissingColumnIndex($table, $db, $editable, $has_unique)
<span id="line1734"></span>{
<span id="line1735"></span>    if (!empty($table) <span><span>&amp;</span></span><span><span>&amp;</span></span> ($GLOBALS['dbi']-&gt;isSystemSchema($db) || !$editable)) {
<span id="line1736"></span>        $missing_unique_column_msg = Message::notice(
<span id="line1737"></span>            sprintf(
<span id="line1738"></span>                __(
<span id="line1739"></span>                    'Current selection does not contain a unique column.'
<span id="line1740"></span>                    . ' Grid edit, checkbox, Edit, Copy and Delete features'
<span id="line1741"></span>                    . ' are not available. %s'
<span id="line1742"></span>                ),
<span id="line1743"></span>                PMA\libraries\Util::showDocu(
<span id="line1744"></span>                    'config',
<span id="line1745"></span>                    'cfg_RowActionLinksWithoutUnique'
<span id="line1746"></span>                )
<span id="line1747"></span>            )
<span id="line1748"></span>        );
<span id="line1749"></span>    } elseif (! empty($table) <span><span>&amp;</span></span><span><span>&amp;</span></span> ! $has_unique) {
<span id="line1750"></span>        $missing_unique_column_msg = Message::notice(
<span id="line1751"></span>            sprintf(
<span id="line1752"></span>                __(
<span id="line1753"></span>                    'Current selection does not contain a unique column.'
<span id="line1754"></span>                    . ' Grid edit, Edit, Copy and Delete features may result in'
<span id="line1755"></span>                    . ' undesired behavior. %s'
<span id="line1756"></span>                ),
<span id="line1757"></span>                PMA\libraries\Util::showDocu(
<span id="line1758"></span>                    'config',
<span id="line1759"></span>                    'cfg_RowActionLinksWithoutUnique'
<span id="line1760"></span>                )
<span id="line1761"></span>            )
<span id="line1762"></span>        );
<span id="line1763"></span>    } else {
<span id="line1764"></span>        $missing_unique_column_msg = null;
<span id="line1765"></span>    }
<span id="line1766"></span>
<span id="line1767"></span>    return $missing_unique_column_msg;
<span id="line1768"></span>}
<span id="line1769"></span>
<span id="line1770"></span>/**
<span id="line1771"></span> * Function to get html to display problems in indexes
<span id="line1772"></span> *
<span id="line1773"></span> * @param string     $query_type     query type
<span id="line1774"></span> * @param array|null $selectedTables array of table names selected from the
<span id="line1775"></span> *                                   database structure page, for an action
<span id="line1776"></span> *                                   like check table, optimize table,
<span id="line1777"></span> *                                   analyze table or repair table
<span id="line1778"></span> * @param string     $db             current database
<span id="line1779"></span> *
<span id="line1780"></span> * @return string
<span id="line1781"></span> */
<span id="line1782"></span>function PMA_getHtmlForIndexesProblems($query_type, $selectedTables, $db)
<span id="line1783"></span>{
<span id="line1784"></span>    // BEGIN INDEX CHECK See if indexes should be checked.
<span id="line1785"></span>    if (isset($query_type)
<span id="line1786"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> $query_type == 'check_tbl'
<span id="line1787"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> isset($selectedTables)
<span id="line1788"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> is_array($selectedTables)
<span id="line1789"></span>    ) {
<span id="line1790"></span>        $indexes_problems_html = '';
<span id="line1791"></span>        foreach ($selectedTables as $tbl_name) {
<span id="line1792"></span>            $check = PMA\libraries\Index::findDuplicates($tbl_name, $db);
<span id="line1793"></span>            if (! empty($check)) {
<span id="line1794"></span>                $indexes_problems_html .= sprintf(
<span id="line1795"></span>                    __('Problems with indexes of table `%s`'), $tbl_name
<span id="line1796"></span>                );
<span id="line1797"></span>                $indexes_problems_html .= $check;
<span id="line1798"></span>            }
<span id="line1799"></span>        }
<span id="line1800"></span>    } else {
<span id="line1801"></span>        $indexes_problems_html = null;
<span id="line1802"></span>    }
<span id="line1803"></span>
<span id="line1804"></span>    return $indexes_problems_html;
<span id="line1805"></span>}
<span id="line1806"></span>
<span id="line1807"></span>/**
<span id="line1808"></span> * Function to display results when the executed query returns non empty results
<span id="line1809"></span> *
<span id="line1810"></span> * @param object         $result               executed query results
<span id="line1811"></span> * @param array          $analyzed_sql_results analysed sql results
<span id="line1812"></span> * @param string         $db                   current database
<span id="line1813"></span> * @param string         $table                current table
<span id="line1814"></span> * @param string         $message              message to show
<span id="line1815"></span> * @param array          $sql_data             sql data
<span id="line1816"></span> * @param DisplayResults $displayResultsObject Instance of DisplayResults
<span id="line1817"></span> * @param string         $pmaThemeImage        uri of the theme image
<span id="line1818"></span> * @param int            $unlim_num_rows       unlimited number of rows
<span id="line1819"></span> * @param int            $num_rows             number of rows
<span id="line1820"></span> * @param string         $disp_query           display query
<span id="line1821"></span> * @param string         $disp_message         display message
<span id="line1822"></span> * @param array          $profiling_results    profiling results
<span id="line1823"></span> * @param string         $query_type           query type
<span id="line1824"></span> * @param array|null     $selectedTables       array of table names selected
<span id="line1825"></span> *                                             from the database structure page, for
<span id="line1826"></span> *                                             an action like check table,
<span id="line1827"></span> *                                             optimize table, analyze table or
<span id="line1828"></span> *                                             repair table
<span id="line1829"></span> * @param string         $sql_query            sql query
<span id="line1830"></span> * @param string         $complete_query       complete sql query
<span id="line1831"></span> *
<span id="line1832"></span> * @return string html
<span id="line1833"></span> */
<span id="line1834"></span>function PMA_getQueryResponseForResultsReturned($result, $analyzed_sql_results,
<span id="line1835"></span>    $db, $table, $message, $sql_data, $displayResultsObject, $pmaThemeImage,
<span id="line1836"></span>    $unlim_num_rows, $num_rows, $disp_query, $disp_message, $profiling_results,
<span id="line1837"></span>    $query_type, $selectedTables, $sql_query, $complete_query
<span id="line1838"></span>) {
<span id="line1839"></span>    // If we are retrieving the full value of a truncated field or the original
<span id="line1840"></span>    // value of a transformed field, show it here
<span id="line1841"></span>    if (isset($_REQUEST['grid_edit']) <span><span>&amp;</span></span><span><span>&amp;</span></span> $_REQUEST['grid_edit'] == true) {
<span id="line1842"></span>        PMA_sendResponseForGridEdit($result);
<span id="line1843"></span>        // script has exited at this point
<span id="line1844"></span>    }
<span id="line1845"></span>
<span id="line1846"></span>    // Gets the list of fields properties
<span id="line1847"></span>    if (isset($result) <span><span>&amp;</span></span><span><span>&amp;</span></span> $result) {
<span id="line1848"></span>        $fields_meta = $GLOBALS['dbi']-&gt;getFieldsMeta($result);
<span id="line1849"></span>    }
<span id="line1850"></span>
<span id="line1851"></span>    // Should be initialized these parameters before parsing
<span id="line1852"></span>    $showtable = isset($showtable) ? $showtable : null;
<span id="line1853"></span>    $url_query = isset($url_query) ? $url_query : null;
<span id="line1854"></span>
<span id="line1855"></span>    $response = PMA\libraries\Response::getInstance();
<span id="line1856"></span>    $header   = $response-&gt;getHeader();
<span id="line1857"></span>    $scripts  = $header-&gt;getScripts();
<span id="line1858"></span>
<span id="line1859"></span>    $just_one_table = PMA_resultSetHasJustOneTable($fields_meta);
<span id="line1860"></span>
<span id="line1861"></span>    // hide edit and delete links:
<span id="line1862"></span>    // - for information_schema
<span id="line1863"></span>    // - if the result set does not contain all the columns of a unique key
<span id="line1864"></span>    //   (unless this is an updatable view)
<span id="line1865"></span>    // - if the SELECT query contains a join or a subquery
<span id="line1866"></span>
<span id="line1867"></span>    $updatableView = false;
<span id="line1868"></span>
<span id="line1869"></span>    $statement = $analyzed_sql_results['statement'];
<span id="line1870"></span>    if ($statement instanceof SqlParser\Statements\SelectStatement) {
<span id="line1871"></span>        if (!empty($statement-&gt;expr)) {
<span id="line1872"></span>            if ($statement-&gt;expr[0]-&gt;expr === '*') {
<span id="line1873"></span>                $_table = new Table($table, $db);
<span id="line1874"></span>                $updatableView = $_table-&gt;isUpdatableView();
<span id="line1875"></span>            }
<span id="line1876"></span>        }
<span id="line1877"></span>
<span id="line1878"></span>        if ($analyzed_sql_results['join']
<span id="line1879"></span>            || $analyzed_sql_results['is_subquery']
<span id="line1880"></span>            || count($analyzed_sql_results['select_tables']) !== 1
<span id="line1881"></span>        ) {
<span id="line1882"></span>            $just_one_table = false;
<span id="line1883"></span>        }
<span id="line1884"></span>    }
<span id="line1885"></span>
<span id="line1886"></span>    $has_unique = PMA_resultSetContainsUniqueKey(
<span id="line1887"></span>        $db, $table, $fields_meta
<span id="line1888"></span>    );
<span id="line1889"></span>
<span id="line1890"></span>    $editable = ($has_unique
<span id="line1891"></span>        || $GLOBALS['cfg']['RowActionLinksWithoutUnique']
<span id="line1892"></span>        || $updatableView)
<span id="line1893"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> $just_one_table;
<span id="line1894"></span>
<span id="line1895"></span>    $displayParts = array(
<span id="line1896"></span>        'edit_lnk' =&gt; $displayResultsObject::UPDATE_ROW,
<span id="line1897"></span>        'del_lnk' =&gt; $displayResultsObject::DELETE_ROW,
<span id="line1898"></span>        'sort_lnk' =&gt; '1',
<span id="line1899"></span>        'nav_bar'  =&gt; '1',
<span id="line1900"></span>        'bkm_form' =&gt; '1',
<span id="line1901"></span>        'text_btn' =&gt; '0',
<span id="line1902"></span>        'pview_lnk' =&gt; '1'
<span id="line1903"></span>    );
<span id="line1904"></span>
<span id="line1905"></span>    if ($GLOBALS['dbi']-&gt;isSystemSchema($db) || !$editable) {
<span id="line1906"></span>        $displayParts = array(
<span id="line1907"></span>            'edit_lnk' =&gt; $displayResultsObject::NO_EDIT_OR_DELETE,
<span id="line1908"></span>            'del_lnk' =&gt; $displayResultsObject::NO_EDIT_OR_DELETE,
<span id="line1909"></span>            'sort_lnk' =&gt; '1',
<span id="line1910"></span>            'nav_bar'  =&gt; '1',
<span id="line1911"></span>            'bkm_form' =&gt; '1',
<span id="line1912"></span>            'text_btn' =&gt; '1',
<span id="line1913"></span>            'pview_lnk' =&gt; '1'
<span id="line1914"></span>        );
<span id="line1915"></span>
<span id="line1916"></span>    }
<span id="line1917"></span>    if (isset($_REQUEST['printview']) <span><span>&amp;</span></span><span><span>&amp;</span></span> $_REQUEST['printview'] == '1') {
<span id="line1918"></span>        $displayParts = array(
<span id="line1919"></span>            'edit_lnk' =&gt; $displayResultsObject::NO_EDIT_OR_DELETE,
<span id="line1920"></span>            'del_lnk' =&gt; $displayResultsObject::NO_EDIT_OR_DELETE,
<span id="line1921"></span>            'sort_lnk' =&gt; '0',
<span id="line1922"></span>            'nav_bar'  =&gt; '0',
<span id="line1923"></span>            'bkm_form' =&gt; '0',
<span id="line1924"></span>            'text_btn' =&gt; '0',
<span id="line1925"></span>            'pview_lnk' =&gt; '0'
<span id="line1926"></span>        );
<span id="line1927"></span>    }
<span id="line1928"></span>
<span id="line1929"></span>    if (isset($_REQUEST['table_maintenance'])) {
<span id="line1930"></span>        $scripts-&gt;addFile('makegrid.js');
<span id="line1931"></span>        $scripts-&gt;addFile('sql.js');
<span id="line1932"></span>        $table_maintenance_html = '';
<span id="line1933"></span>        if (isset($message)) {
<span id="line1934"></span>            $message = Message::success($message);
<span id="line1935"></span>            $table_maintenance_html = PMA\libraries\Util::getMessage(
<span id="line1936"></span>                $message, $GLOBALS['sql_query'], 'success'
<span id="line1937"></span>            );
<span id="line1938"></span>        }
<span id="line1939"></span>        $table_maintenance_html .= PMA_getHtmlForSqlQueryResultsTable(
<span id="line1940"></span>            $displayResultsObject,
<span id="line1941"></span>            $pmaThemeImage, $url_query, $displayParts,
<span id="line1942"></span>            false, $unlim_num_rows, $num_rows, $showtable, $result,
<span id="line1943"></span>            $analyzed_sql_results
<span id="line1944"></span>        );
<span id="line1945"></span>        if (empty($sql_data) || ($sql_data['valid_queries'] = 1)) {
<span id="line1946"></span>            $response-&gt;addHTML($table_maintenance_html);
<span id="line1947"></span>            exit();
<span id="line1948"></span>        }
<span id="line1949"></span>    }
<span id="line1950"></span>
<span id="line1951"></span>    if (!isset($_REQUEST['printview']) || $_REQUEST['printview'] != '1') {
<span id="line1952"></span>        $scripts-&gt;addFile('makegrid.js');
<span id="line1953"></span>        $scripts-&gt;addFile('sql.js');
<span id="line1954"></span>        unset($GLOBALS['message']);
<span id="line1955"></span>        //we don't need to buffer the output in getMessage here.
<span id="line1956"></span>        //set a global variable and check against it in the function
<span id="line1957"></span>        $GLOBALS['buffer_message'] = false;
<span id="line1958"></span>    }
<span id="line1959"></span>
<span id="line1960"></span>    $previous_update_query_html = PMA_getHtmlForPreviousUpdateQuery(
<span id="line1961"></span>        isset($disp_query) ? $disp_query : null,
<span id="line1962"></span>        $GLOBALS['cfg']['ShowSQL'], isset($sql_data) ? $sql_data : null,
<span id="line1963"></span>        isset($disp_message) ? $disp_message : null
<span id="line1964"></span>    );
<span id="line1965"></span>
<span id="line1966"></span>    $profiling_chart_html = PMA_getHtmlForProfilingChart(
<span id="line1967"></span>        $url_query, $db, isset($profiling_results) ? $profiling_results :array()
<span id="line1968"></span>    );
<span id="line1969"></span>
<span id="line1970"></span>    $missing_unique_column_msg = PMA_getMessageIfMissingColumnIndex(
<span id="line1971"></span>        $table, $db, $editable, $has_unique
<span id="line1972"></span>    );
<span id="line1973"></span>
<span id="line1974"></span>    $bookmark_created_msg = PMA_getBookmarkCreatedMessage();
<span id="line1975"></span>
<span id="line1976"></span>    $table_html = PMA_getHtmlForSqlQueryResultsTable(
<span id="line1977"></span>        $displayResultsObject,
<span id="line1978"></span>        $pmaThemeImage, $url_query, $displayParts,
<span id="line1979"></span>        $editable, $unlim_num_rows, $num_rows, $showtable, $result,
<span id="line1980"></span>        $analyzed_sql_results
<span id="line1981"></span>    );
<span id="line1982"></span>
<span id="line1983"></span>    $indexes_problems_html = PMA_getHtmlForIndexesProblems(
<span id="line1984"></span>        isset($query_type) ? $query_type : null,
<span id="line1985"></span>        isset($selectedTables) ? $selectedTables : null, $db
<span id="line1986"></span>    );
<span id="line1987"></span>
<span id="line1988"></span>    $cfgBookmark = PMA_Bookmark_getParams();
<span id="line1989"></span>    if ($cfgBookmark) {
<span id="line1990"></span>        $bookmark_support_html = PMA_getHtmlForBookmark(
<span id="line1991"></span>            $displayParts,
<span id="line1992"></span>            $cfgBookmark,
<span id="line1993"></span>            $sql_query, $db, $table,
<span id="line1994"></span>            isset($complete_query) ? $complete_query : $sql_query,
<span id="line1995"></span>            $cfgBookmark['user']
<span id="line1996"></span>        );
<span id="line1997"></span>    } else {
<span id="line1998"></span>        $bookmark_support_html = '';
<span id="line1999"></span>    }
<span id="line2000"></span>
<span id="line2001"></span>    $html_output = isset($table_maintenance_html) ? $table_maintenance_html : '';
<span id="line2002"></span>
<span id="line2003"></span>    $html_output .= PMA_getHtmlForSqlQueryResults(
<span id="line2004"></span>        $previous_update_query_html, $profiling_chart_html,
<span id="line2005"></span>        $missing_unique_column_msg, $bookmark_created_msg,
<span id="line2006"></span>        $table_html, $indexes_problems_html, $bookmark_support_html
<span id="line2007"></span>    );
<span id="line2008"></span>
<span id="line2009"></span>    return $html_output;
<span id="line2010"></span>}
<span id="line2011"></span>
<span id="line2012"></span>/**
<span id="line2013"></span> * Function to execute the query and send the response
<span id="line2014"></span> *
<span id="line2015"></span> * @param array      $analyzed_sql_results   analysed sql results
<span id="line2016"></span> * @param bool       $is_gotofile            whether goto file or not
<span id="line2017"></span> * @param string     $db                     current database
<span id="line2018"></span> * @param string     $table                  current table
<span id="line2019"></span> * @param bool|null  $find_real_end          whether to find real end or not
<span id="line2020"></span> * @param string     $sql_query_for_bookmark the sql query to be stored as bookmark
<span id="line2021"></span> * @param array|null $extra_data             extra data
<span id="line2022"></span> * @param string     $message_to_show        message to show
<span id="line2023"></span> * @param string     $message                message
<span id="line2024"></span> * @param array|null $sql_data               sql data
<span id="line2025"></span> * @param string     $goto                   goto page url
<span id="line2026"></span> * @param string     $pmaThemeImage          uri of the PMA theme image
<span id="line2027"></span> * @param string     $disp_query             display query
<span id="line2028"></span> * @param string     $disp_message           display message
<span id="line2029"></span> * @param string     $query_type             query type
<span id="line2030"></span> * @param string     $sql_query              sql query
<span id="line2031"></span> * @param array|null $selectedTables         array of table names selected from the
<span id="line2032"></span> *                                           database structure page, for an action
<span id="line2033"></span> *                                           like check table, optimize table,
<span id="line2034"></span> *                                           analyze table or repair table
<span id="line2035"></span> * @param string     $complete_query         complete query
<span id="line2036"></span> *
<span id="line2037"></span> * @return void
<span id="line2038"></span> */
<span id="line2039"></span>function PMA_executeQueryAndSendQueryResponse($analyzed_sql_results,
<span id="line2040"></span>    $is_gotofile, $db, $table, $find_real_end, $sql_query_for_bookmark,
<span id="line2041"></span>    $extra_data, $message_to_show, $message, $sql_data, $goto, $pmaThemeImage,
<span id="line2042"></span>    $disp_query, $disp_message, $query_type, $sql_query, $selectedTables,
<span id="line2043"></span>    $complete_query
<span id="line2044"></span>) {
<span id="line2045"></span>    if ($analyzed_sql_results == null) {
<span id="line2046"></span>        // Parse and analyze the query
<span id="line2047"></span>        include_once 'libraries/parse_analyze.lib.php';
<span id="line2048"></span>        list(
<span id="line2049"></span>            $analyzed_sql_results,
<span id="line2050"></span>            $db,
<span id="line2051"></span>            $table_from_sql
<span id="line2052"></span>        ) = PMA_parseAnalyze($sql_query, $db);
<span id="line2053"></span>        // @todo: possibly refactor
<span id="line2054"></span>        extract($analyzed_sql_results);
<span id="line2055"></span>
<span id="line2056"></span>        if ($table != $table_from_sql <span><span>&amp;</span></span><span><span>&amp;</span></span> !empty($table_from_sql)) {
<span id="line2057"></span>            $table = $table_from_sql;
<span id="line2058"></span>        }
<span id="line2059"></span>    }
<span id="line2060"></span>
<span id="line2061"></span>    $html_output = PMA_executeQueryAndGetQueryResponse(
<span id="line2062"></span>        $analyzed_sql_results, // analyzed_sql_results
<span id="line2063"></span>        $is_gotofile, // is_gotofile
<span id="line2064"></span>        $db, // db
<span id="line2065"></span>        $table, // table
<span id="line2066"></span>        $find_real_end, // find_real_end
<span id="line2067"></span>        $sql_query_for_bookmark, // sql_query_for_bookmark
<span id="line2068"></span>        $extra_data, // extra_data
<span id="line2069"></span>        $message_to_show, // message_to_show
<span id="line2070"></span>        $message, // message
<span id="line2071"></span>        $sql_data, // sql_data
<span id="line2072"></span>        $goto, // goto
<span id="line2073"></span>        $pmaThemeImage, // pmaThemeImage
<span id="line2074"></span>        $disp_query, // disp_query
<span id="line2075"></span>        $disp_message, // disp_message
<span id="line2076"></span>        $query_type, // query_type
<span id="line2077"></span>        $sql_query, // sql_query
<span id="line2078"></span>        $selectedTables, // selectedTables
<span id="line2079"></span>        $complete_query // complete_query
<span id="line2080"></span>    );
<span id="line2081"></span>
<span id="line2082"></span>    $response = PMA\libraries\Response::getInstance();
<span id="line2083"></span>    $response-&gt;addHTML($html_output);
<span id="line2084"></span>}
<span id="line2085"></span>
<span id="line2086"></span>/**
<span id="line2087"></span> * Function to execute the query and send the response
<span id="line2088"></span> *
<span id="line2089"></span> * @param array      $analyzed_sql_results   analysed sql results
<span id="line2090"></span> * @param bool       $is_gotofile            whether goto file or not
<span id="line2091"></span> * @param string     $db                     current database
<span id="line2092"></span> * @param string     $table                  current table
<span id="line2093"></span> * @param bool|null  $find_real_end          whether to find real end or not
<span id="line2094"></span> * @param string     $sql_query_for_bookmark the sql query to be stored as bookmark
<span id="line2095"></span> * @param array|null $extra_data             extra data
<span id="line2096"></span> * @param string     $message_to_show        message to show
<span id="line2097"></span> * @param string     $message                message
<span id="line2098"></span> * @param array|null $sql_data               sql data
<span id="line2099"></span> * @param string     $goto                   goto page url
<span id="line2100"></span> * @param string     $pmaThemeImage          uri of the PMA theme image
<span id="line2101"></span> * @param string     $disp_query             display query
<span id="line2102"></span> * @param string     $disp_message           display message
<span id="line2103"></span> * @param string     $query_type             query type
<span id="line2104"></span> * @param string     $sql_query              sql query
<span id="line2105"></span> * @param array|null $selectedTables         array of table names selected from the
<span id="line2106"></span> *                                           database structure page, for an action
<span id="line2107"></span> *                                           like check table, optimize table,
<span id="line2108"></span> *                                           analyze table or repair table
<span id="line2109"></span> * @param string     $complete_query         complete query
<span id="line2110"></span> *
<span id="line2111"></span> * @return string html
<span id="line2112"></span> */
<span id="line2113"></span>function PMA_executeQueryAndGetQueryResponse($analyzed_sql_results,
<span id="line2114"></span>    $is_gotofile, $db, $table, $find_real_end, $sql_query_for_bookmark,
<span id="line2115"></span>    $extra_data, $message_to_show, $message, $sql_data, $goto, $pmaThemeImage,
<span id="line2116"></span>    $disp_query, $disp_message, $query_type, $sql_query, $selectedTables,
<span id="line2117"></span>    $complete_query
<span id="line2118"></span>) {
<span id="line2119"></span>    // Handle disable/enable foreign key checks
<span id="line2120"></span>    $default_fk_check = PMA\libraries\Util::handleDisableFKCheckInit();
<span id="line2121"></span>
<span id="line2122"></span>    // Handle remembered sorting order, only for single table query.
<span id="line2123"></span>    // Handling is not required when it's a union query
<span id="line2124"></span>    // (the parser never sets the 'union' key to 0).
<span id="line2125"></span>    // Handling is also not required if we came from the "Sort by key"
<span id="line2126"></span>    // drop-down.
<span id="line2127"></span>    if (! empty($analyzed_sql_results)
<span id="line2128"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> PMA_isRememberSortingOrder($analyzed_sql_results)
<span id="line2129"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> empty($analyzed_sql_results['union'])
<span id="line2130"></span>        <span><span>&amp;</span></span><span><span>&amp;</span></span> ! isset($_REQUEST['sort_by_key'])
<span id="line2131"></span>    ) {
<span id="line2132"></span>        if (! isset($_SESSION['sql_from_query_box'])) {
<span id="line2133"></span>            PMA_handleSortOrder($db, $table, $analyzed_sql_results, $sql_query);
<span id="line2134"></span>        } else {
<span id="line2135"></span>            unset($_SESSION['sql_from_query_box']);
<span id="line2136"></span>        }
<span id="line2137"></span>
<span id="line2138"></span>    }
<span id="line2139"></span>
<span id="line2140"></span>    $displayResultsObject = new PMA\libraries\DisplayResults(
<span id="line2141"></span>        $GLOBALS['db'], $GLOBALS['table'], $goto, $sql_query
<span id="line2142"></span>    );
<span id="line2143"></span>    $displayResultsObject-&gt;setConfigParamsForDisplayTable();
<span id="line2144"></span>
<span id="line2145"></span>    // assign default full_sql_query
<span id="line2146"></span>    $full_sql_query = $sql_query;
<span id="line2147"></span>
<span id="line2148"></span>    // Do append a "LIMIT" clause?
<span id="line2149"></span>    if (PMA_isAppendLimitClause($analyzed_sql_results)) {
<span id="line2150"></span>        $full_sql_query = PMA_getSqlWithLimitClause($analyzed_sql_results);
<span id="line2151"></span>    }
<span id="line2152"></span>
<span id="line2153"></span>    $GLOBALS['reload'] = PMA_hasCurrentDbChanged($db);
<span id="line2154"></span>    $GLOBALS['dbi']-&gt;selectDb($db);
<span id="line2155"></span>
<span id="line2156"></span>    // Execute the query
<span id="line2157"></span>    list($result, $num_rows, $unlim_num_rows, $profiling_results, $extra_data)
<span id="line2158"></span>        = PMA_executeTheQuery(
<span id="line2159"></span>            $analyzed_sql_results,
<span id="line2160"></span>            $full_sql_query,
<span id="line2161"></span>            $is_gotofile,
<span id="line2162"></span>            $db,
<span id="line2163"></span>            $table,
<span id="line2164"></span>            isset($find_real_end) ? $find_real_end : null,
<span id="line2165"></span>            isset($sql_query_for_bookmark) ? $sql_query_for_bookmark : null,
<span id="line2166"></span>            isset($extra_data) ? $extra_data : null
<span id="line2167"></span>        );
<span id="line2168"></span>
<span id="line2169"></span>    // No rows returned -&gt; move back to the calling page
<span id="line2170"></span>    if ((0 == $num_rows <span><span>&amp;</span></span><span><span>&amp;</span></span> 0 == $unlim_num_rows)
<span id="line2171"></span>        || $analyzed_sql_results['is_affected']
<span id="line2172"></span>    ) {
<span id="line2173"></span>        $html_output = PMA_getQueryResponseForNoResultsReturned(
<span id="line2174"></span>            $analyzed_sql_results, $db, $table,
<span id="line2175"></span>            isset($message_to_show) ? $message_to_show : null,
<span id="line2176"></span>            $num_rows, $displayResultsObject, $extra_data,
<span id="line2177"></span>            $pmaThemeImage, isset($result) ? $result : null,
<span id="line2178"></span>            $sql_query, isset($complete_query) ? $complete_query : null
<span id="line2179"></span>        );
<span id="line2180"></span>    } else {
<span id="line2181"></span>        // At least one row is returned -&gt; displays a table with results
<span id="line2182"></span>        $html_output = PMA_getQueryResponseForResultsReturned(
<span id="line2183"></span>            isset($result) ? $result : null,
<span id="line2184"></span>            $analyzed_sql_results,
<span id="line2185"></span>            $db,
<span id="line2186"></span>            $table,
<span id="line2187"></span>            isset($message) ? $message : null,
<span id="line2188"></span>            isset($sql_data) ? $sql_data : null,
<span id="line2189"></span>            $displayResultsObject,
<span id="line2190"></span>            $pmaThemeImage,
<span id="line2191"></span>            $unlim_num_rows,
<span id="line2192"></span>            $num_rows,
<span id="line2193"></span>            isset($disp_query) ? $disp_query : null,
<span id="line2194"></span>            isset($disp_message) ? $disp_message : null,
<span id="line2195"></span>            $profiling_results,
<span id="line2196"></span>            isset($query_type) ? $query_type : null,
<span id="line2197"></span>            isset($selectedTables) ? $selectedTables : null,
<span id="line2198"></span>            $sql_query,
<span id="line2199"></span>            isset($complete_query) ? $complete_query : null
<span id="line2200"></span>        );
<span id="line2201"></span>    }
<span id="line2202"></span>
<span id="line2203"></span>    // Handle disable/enable foreign key checks
<span id="line2204"></span>    PMA\libraries\Util::handleDisableFKCheckCleanup($default_fk_check);
<span id="line2205"></span>
<span id="line2206"></span>    return $html_output;
<span id="line2207"></span>}
<span id="line2208"></span>
<span id="line2209"></span>/**
<span id="line2210"></span> * Function to define pos to display a row
<span id="line2211"></span> *
<span id="line2212"></span> * @param Int $number_of_line Number of the line to display
<span id="line2213"></span> * @param Int $max_rows       Number of rows by page
<span id="line2214"></span> *
<span id="line2215"></span> * @return Int Start position to display the line
<span id="line2216"></span> */
<span id="line2217"></span>function PMA_getStartPosToDisplayRow($number_of_line, $max_rows = null)
<span id="line2218"></span>{
<span id="line2219"></span>    if (null === $max_rows) {
<span id="line2220"></span>        $max_rows = $_SESSION['tmpval']['max_rows'];
<span id="line2221"></span>    }
<span id="line2222"></span>
<span id="line2223"></span>    return @((ceil($number_of_line / $max_rows) - 1) * $max_rows);
<span id="line2224"></span>}
<span id="line2225"></span>
<span id="line2226"></span>/**
<span id="line2227"></span> * Function to calculate new pos if pos is higher than number of rows
<span id="line2228"></span> * of displayed table
<span id="line2229"></span> *
<span id="line2230"></span> * @param String   $db    Database name
<span id="line2231"></span> * @param String   $table Table name
<span id="line2232"></span> * @param Int|null $pos   Initial position
<span id="line2233"></span> *
<span id="line2234"></span> * @return Int Number of pos to display last page
<span id="line2235"></span> */
<span id="line2236"></span>function PMA_calculatePosForLastPage($db, $table, $pos)
<span id="line2237"></span>{
<span id="line2238"></span>    if (null === $pos) {
<span id="line2239"></span>        $pos = $_SESSION['tmpval']['pos'];
<span id="line2240"></span>    }
<span id="line2241"></span>
<span id="line2242"></span>    $_table = new Table($table, $db);
<span id="line2243"></span>    $unlim_num_rows = $_table-&gt;countRecords(true);
<span id="line2244"></span>    //If position is higher than number of rows
<span id="line2245"></span>    if ($unlim_num_rows </span><span class="error" title="Bad character after “&lt;”. Probable cause: Unescaped “&lt;”. Try escaping it as “&amp;lt;”.">&lt;=</span><span> $pos <span><span>&amp;</span></span><span><span>&amp;</span></span> 0 != $pos) {
<span id="line2246"></span>        $pos = PMA_getStartPosToDisplayRow($unlim_num_rows);
<span id="line2247"></span>    }
<span id="line2248"></span>
<span id="line2249"></span>    return $pos;
<span id="line2250"></span>}
<span id="line2251"></span></span></pre><menu type="context" id="actions"><menuitem id="goToLine" label="Go to Line…" accesskey="L"></menuitem><menuitem id="wrapLongLines" label="Wrap Long Lines" type="checkbox"></menuitem><menuitem id="highlightSyntax" label="Syntax Highlighting" type="checkbox" checked="true"></menuitem></menu></body></html>
