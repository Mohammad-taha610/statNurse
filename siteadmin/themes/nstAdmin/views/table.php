@extends('master')
@section('site-container')
<?php

use sacore\application\app;
use sacore\utilities\stringUtils;

if (is_array($table))
{
    foreach( $table as $tableKey=>$singleTable )
    {
        if (isset($viewParams[0]))
        {
            if ($tableKey!=$viewParams[0])
            {
                continue;
            }
        }

        if (empty($singleTable['noDataMessage']))
            $singleTable['noDataMessage'] = 'No data is currently available.';

        if (!isset($singleTable['sortable']))
            $singleTable['sortable'] = true;

        if ( !empty( $singleTable['title'] ) )
        {
            ?>
            <h4 class="header blue bolder smaller"><?=$singleTable['title']?></h4>
            <?php
        }

        ?>
        <div class="row">
            <div class="col-xs-12">

                <?php
                if (  $singleTable['searchable'] )
                {
                    $hideSearch = false;

                    if($_GET['hideSearch']) {
                        $hideSearch = true;
                    }

                    ?>
                    <div id="search_<?=$tableKey?>" class="<?= ($_GET['action']=='search' || $singleTable['showSearchOpen']) && !$hideSearch  ? '' : 'saHidden' ?>">
                        <div class="well">
							<span class="blue font20">
								Search
							</span>
                            <hr>
                            <form method="get">
                                <input type="hidden" value="search" name="action">
                                <div>
                                    <?php
                                    foreach( $singleTable['header'] as $key=>$column ) {
                                        $columnName = $column;
                                        $class = '';
                                        $searchable = true;
                                        if (is_array($column)) {
                                            $columnName = $column['name'];
                                            $class = $column['class'];
                                            $searchable = isset($column['searchable']) ? $column['searchable'] : true;
                                            $placeholder = isset($column['placeholder']) ? $column['placeholder'] : '';
                                        }

                                        if (!$searchable) continue;


                                        if ((!empty($singleTable['header'][$key]['map']) || !empty($singleTable['map'][$key]) || !empty($singleTable['header'][$key]['sort'])) && $singleTable['sortable']) {
                                            if (!empty($singleTable['header'][$key]['sort']))
                                                $dbField = $singleTable['header'][$key]['sort'];
                                            elseif (!empty($singleTable['header'][$key]['map']))
                                                $dbField = $singleTable['header'][$key]['map'];
                                            elseif (!empty($singleTable['map'][$key]))
                                                $dbField = $singleTable['map'][$key];
                                        }

                                        $dbField = str_replace('.', '!', $dbField);
                                        if ($column['searchType'] == 'select-boolean') { ?>
                                            <div class="form-group">
                                                <div class="row">
                                                    <label class="col-sm-2 control-label no-padding-right"
                                                           for="form-field-to"><?= $columnName ?>: </label>

                                                    <div class="col-sm-10">
                                                        <select name="q_<?= $dbField ?>">
                                                            <option value="" <?=($_GET['q_' . $dbField] === null || $_GET['q_' . $dbField] === '') ? 'selected' : ''?>>---SELECT---</option>
                                                            <option value="1" <?=($_GET['q_' . $dbField] == '1') ? 'selected' : ''?>>Yes</option>
                                                            <option value="0" <?=($_GET['q_' . $dbField] === '0') ? 'selected' : ''?>>No</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } else if($column['searchType'] == 'select') { ?>
                                            <div class="form-group">
                                                <div class="row">
                                                    <label class="col-sm-2 control-label no-padding-right"
                                                           for="form-field-to"><?= $columnName ?>: </label>

                                                    <div class="col-sm-10">
                                                        <select name="q_<?= $dbField ?>" title="Select">
                                                            <option value="">---SELECT---</option>
                                                            <?php
                                                            foreach($column['values'] as $key => $value) {
                                                                $url_field_param = $_GET['q_' . $dbField];
                                                                $selected = (!empty($url_field_param) && $url_field_param == $value) ? 'selected' : '';
                                                                ?>
                                                                <option value="<?= $key ?>" <?= $selected ?>>
                                                                    <?= $value ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php } else if($column['searchType'] == 'integer') { ?>
                                            <div class="form-group">
                                                <div class="row">
                                                    <label class="col-sm-2 control-label no-padding-right"
                                                           for="form-field-to"><?= $columnName ?>: </label>

                                                    <div class="col-sm-10">
                                                        <input class="col-xs-12 col-sm-10" type="number"
                                                               name="q_<?= $dbField ?>"
                                                               value="<?= $_GET['q_' . $dbField] ?>"/>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } else if($column['searchType'] == 'date') { ?>
                                            <div class="form-group">
                                                <div class="row">
                                                    <label class="col-sm-2 control-label no-padding-right"
                                                           for="form-field-to"><?= $columnName ?>: </label>

                                                    <div class="col-sm-10">
                                                        <input class="col-xs-12 col-sm-10" type="date"
                                                               name="q_<?= $dbField ?>"
                                                               value="<?= $_GET['q_' . $dbField] ?>"/>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } else { ?>
                                            <div class="form-group">
                                                <div class="row">
                                                    <label class="col-sm-2 control-label no-padding-right"
                                                           for="form-field-to"><?= $columnName ?>: </label>

                                                    <div class="col-sm-10">
                                                        <input class="col-xs-12 col-sm-10" type="text"
                                                               name="q_<?= $dbField ?>"
                                                               value="<?= $_GET['q_' . $dbField] ?>"
                                                               placeholder="<?= $placeholder ?>"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }

                                    if (is_countable($singleTable['custom_search_fields']) && is_array($singleTable['custom_search_fields']) && count($singleTable['custom_search_fields']) > 0) {
                                        foreach ($singleTable['custom_search_fields'] as $k => $v) {
                                            if (is_array($v)) {
                                                if ($v['searchType'] === 'radios') { ?>
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <label class="col-sm-2 control-label no-padding-right"
                                                                   for="form-field-to"><?= $v['label'] ?>: </label>
                                                            <div class="col-sm-10">
                                                                <?php foreach ($v['items'] as $k2 => $v2) { ?>
                                                                    <div class="radio">
                                                                        <label>
                                                                            <input type="radio" name="q_application_status"
                                                                                   id="<?= $k2 ?>"
                                                                                   value="<?= $k2 ?>" <?= $_GET['q_' . $v['name']] === $k2 ? 'checked' : '' ?>>
                                                                            <?= $v2['label']; ?>
                                                                        </label>
                                                                    </div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <label class="col-sm-2 control-label no-padding-right"
                                                                   for="form-field-to"><?= $v['name'] ?>: </label>
                                                            <div class="col-sm-10">
                                                                <input class="col-xs-12 col-sm-10"
                                                                       type=<?= ($v['searchType']) ?? $v['searchType'] ?? 'text' ?> name="q_<?= $k ?>"
                                                                       value="<?= $_GET['q_' . $k] ?>" <?= $v['extraOptions'] ?> />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                            } else {
                                                ?>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <label class="col-sm-2 control-label no-padding-right"
                                                               for="form-field-to"><?= $v ?>: </label>
                                                        <div class="col-sm-10">
                                                            <input class="col-xs-12 col-sm-10" type="text"
                                                                   name="q_<?= $k ?>" value="<?= $_GET['q_' . $k] ?>"/>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button class="btn btn-info" type="submit">
                                            <i class="fa fa-save bigger-110"></i>
                                            Search
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                }
                ?>

                <div class="table-responsive dataTables_wrapper">
                    <table id="sample-table-1" class="table table-striped table-bordered table-hover <?= !empty($singleTable['class']) ? $singleTable['class'] : '' ?>" style="margin-bottom: 0px">						<thead>
                        <tr>

                            <?php

                            if (isset($singleTable['massActions']) && is_array($singleTable['massActions'])) {
                                echo '<th width="30"><input type="checkbox" class="mass-select-all" data-key="'.$tableKey.'" id="mass-select-all-'.$tableKey.'" /> </th>';
                            }

                            foreach( $singleTable['header'] as $key=>$column )
                            {

                                $columnName = $column;
                                $class = '';
                                if (is_array($column))
                                {
                                    $columnName = $column['name'];
                                    $class = $column['class'];
                                }

                                if (!isset($singleTable['header'][ $key ]['sortable'])) {
                                    $singleTable['header'][ $key ]['sortable'] = true;
                                }


                                if ( (!empty( $singleTable['header'][ $key ]['map'] ) ||
                                        !empty( $singleTable['map'][ $key ] ) ||
                                        !empty( $singleTable['header'][ $key ]['sort'] ))
                                    && $singleTable['sortable']
                                    && $singleTable['header'][ $key ]['sortable'] )
                                {
                                    $caret = '<i class="fa fa-sort pull-right"></i>';

                                    if (!empty( $singleTable['header'][ $key ]['sort'] ))
                                        $dbField = $singleTable['header'][ $key ]['sort'];
                                    elseif (!empty( $singleTable['header'][ $key ]['map'] ))
                                        $dbField = $singleTable['header'][ $key ]['map'];
                                    elseif (!empty( $singleTable['map'][ $key ] ))
                                        $dbField = $singleTable['map'][ $key ];

                                    $defaultOrderBy = $singleTable['defaultOrderBy'];

                                    $requestString = explode('&', explode('?',$_SERVER['REQUEST_URI'])[1]);
                                    $queryArray = [];
                                    foreach ($requestString as $request){
                                        [$queryKey, $value] = explode('=', $request);
                                        $queryArray[$queryKey] = $value;
                                    }

                                    $sortDir = 'ASC';
                                    if ($dbField == $queryArray['sort'] || $defaultOrderBy['col'] == $dbField)
                                    {
                                        $caret = '<i class="fa fa-caret-up pull-right"></i>';

                                        if ($queryArray['sortDir'] == 'ASC' || $defaultOrderBy['dir'] == 'ASC')
                                        {
                                            $caret = '<i class="fa fa-caret-down pull-right"></i>';
                                            $sortDir = 'DESC';
                                        }
                                    }
                                }

                                $qsParts = $_GET;
                                $qsParts['sort'] = $dbField;
                                $qsParts['sortDir'] = $sortDir;
                                $qs = http_build_query($qsParts);

                                if ($singleTable['sortable'] && $singleTable['header'][ $key ]['sortable'])
                                {
                                    ?>
                                    <th class="<?=$class?>">
                                        <a class="" href="?<?=$qs?>"> <?=$columnName?></a>
                                        <?=$caret?>
                                    </th>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <th class="<?=$class?>">
                                        <?=$columnName?>
                                    </th>
                                    <?php
                                }

                            }

                            if ( isset($singleTable['actions']) || isset($singleTable['headerActions']) )
                            {
                                echo '<th  class="text-right">';
                            }

                            if ( isset($singleTable['headerActions']) )
                            {

                                foreach($singleTable['headerActions'] as $key=>$action)
                                {
                                    $params = $singleTable['headerActions'][$key]['params'];

                                    if (!is_array($params))
                                        $params = array();

                                    $routeId = $singleTable['headerActions'][$key]['routeid'];
                                    $args = array( $singleTable['headerActions'][$key]['routeid'] );
                                    $args = array_merge( $args, $params );
                                    $aLink = app::get()->getRouter()->generate($routeId, $params);

                                    if (!empty($action['querystring']))
                                        $aLink .= '?'.$action['querystring'];

                                    $data = '';
                                    if (!empty($action['data']) && is_array($action['data']))
                                    {
                                        foreach($action['data'] as $dataKey=>$value)
                                        {
                                            $data .= 'data-'.$dataKey.'="'.$value.'" ';
                                        }
                                    }


                                    if (!empty($action['icon']))
                                        echo ' <a href="'.$aLink.'" title="'.$action['name'].'" '.$data.' class="btn btn-xs btn-primary '.$action['class'].'">
														<i class="fa fa-'.$action['icon'].' bigger-120"></i>
														'.($action['showText'] ? $action['name'] : '').'
													</a> ';
                                    else
                                        echo ' <a href="'.$aLink.'" title="'.$action['name'].'" '.$data.' class="btn btn-xs btn-info  '.$action['class'].'">'.$action['name'].'</a>';
                                }

                            }

                            if ( isset($singleTable['actions']) )
                            {
                                if ($_GET['action'] && $_GET['action'] === 'search') {
                                    ?>
                                    <button class="btn btn-xs btn-danger" onclick="window.location = '<?= app::get()->getConfiguration()->get('secure_site_url') ?>/siteadmin/nurse/applications'; return false;">Clear Search Filter</button>
                                    <?php
                                }

                                if (  $singleTable['searchable'] ) {
                                    ?>
                                    <button class="btn btn-xs btn-primary" onclick="$('#search_<?=$tableKey?>').slideToggle('50')">Search</button>
                                    <?php
                                }

                                if (  !empty($singleTable['tableCreateRoute']) )
                                {

                                    if (is_array($singleTable['tableCreateRoute']))
                                    {
                                        $params = isset($singleTable['tableCreateRoute']['params']) ? $singleTable['tableCreateRoute']['params'] : array();
                                        if (isset($singleTable['tableCreateRoute']['routeId']))
                                            $routeid = $singleTable['tableCreateRoute']['routeId'];
                                        elseif (isset($singleTable['tableCreateRoute']['routeid']))
                                            $routeid = $singleTable['tableCreateRoute']['routeid'];
                                    }
                                    else
                                    {
                                        $routeid = $singleTable['tableCreateRoute'];
                                        $params = array();
                                    }

                                    $args = array( $routeid );
                                    $args = array_merge($args, $params);
                                    $link = app::get()->getRouter()->generate($routeid,$params);

                                    if (!empty($action['querystring']))
                                        $link .= $action['querystring'];

                                    echo '<a href="'.$link.'" class="btn btn-xs btn-info">
													<i class="fa fa-plus bigger-120"></i>
												</a>';

                                }
                            }

                            if ( isset($singleTable['actions']) || isset($singleTable['headerActions']) )
                            {
                                echo '</th>';
                            }

                            ?>
                        </tr>
                        </thead>

                        <tbody>
                        <?php
                        if (is_array($singleTable['data']) and count($singleTable['data'])>0)
                        {
                            foreach( $singleTable['data'] as $row )
                            {
                                if (is_object($row))
                                {
                                    $row = $row->toArray();
                                }

                                if ( is_callable($singleTable['dataRowCallback']) ) {

                                    $row = $singleTable['dataRowCallback']($row);

                                }

                                $enableActions  = true;
                                if ( isset($singleTable['disableActionsPerRowBoolField']) )
                                {
                                    $enableActions = $row[ $singleTable['disableActionsPerRowBoolField'] ];
                                }

                                echo '<tr>';

                                if (isset($singleTable['massActions']) && is_array($singleTable['massActions']) && $singleTable['massActionsCheckboxValue']) {
                                    echo '<td>
													<input type="checkbox" value="'.$row[ $singleTable['massActionsCheckboxValue'] ].'" class="mass-select" data-key="'.$tableKey.'" />
												  </td>';
                                }

                                foreach( $singleTable['header'] as $key=>$column )
                                {
                                    $tdClass = '';
                                    if (is_array($column))
                                    {
                                        $columnName = $column['name'];
                                        $tdClass = $column['tdClass'];
                                    }

                                    echo '<td class="'.$tdClass.'">';

                                    // SETUP ROW CLICK EDIT ACTION
                                    if (!empty($singleTable['actions']['edit']) && $enableActions)
                                    {
                                        $params = $singleTable['actions']['edit']['params'];

                                        if (!is_array($params))
                                            $params = array();

                                        $rowParams = [];
                                        foreach($params as $paramKey=>$param)
                                        {
                                            $splitData = $row;
                                            $mapParts = explode('.', $param);

                                            if ( is_array($row) && count($mapParts)>1 ) {

                                                foreach ($mapParts as $part) {

                                                    if (is_array($splitData)) {
                                                        $splitData = $splitData[$part];
                                                    }
                                                }

                                                $rowParams[$param] = $splitData;
                                            }
                                            elseif (array_key_exists($param, $row))
                                                $rowParams[$param] = $row[ $param ];

                                        }


                                        //Old way, may be missing something in new implementation
                                        $args = array( $singleTable['actions']['edit']['routeid'] );

                                        $args = array_merge( $args, $params );
//												$editLink = call_user_func_array(array($url,'make'), $args);

                                        $routeId = $singleTable['actions']['edit']['routeid'];
                                        $editLink = app::get()->getRouter()->generate($routeId, $rowParams);

                                        if (!empty($singleTable['actions']['edit']['target']))
                                            $aTarget = 'target="'.$singleTable['actions']['edit']['target'].'"';

                                        echo '<a '.$aTarget.' href="'.$editLink.'" title="'.$singleTable['actions']['edit']['name'].'">';
                                    }

                                    // SETUP INDIVIDUAL CELLS LINK
                                    if (!empty($singleTable['header'][ $key ]['link']) && $enableActions)
                                    {
                                        $params = $singleTable['header'][ $key ]['link']['params'];

                                        if (!is_array($params))
                                            $params = array();

                                        $rowParams = [];
                                        foreach($params as $paramKey=>$param)
                                        {
                                            $splitData = $row;
                                            $mapParts = explode('.', $param);

                                            if ( is_array($row) && count($mapParts)>1 ) {

                                                foreach ($mapParts as $part) {

                                                    if (is_array($splitData)) {
                                                        $splitData = $splitData[$part];
                                                    }
                                                }

                                                $rowParams[$paramKey] = $splitData;
                                            }
                                            elseif (array_key_exists($param, $row))
                                                $rowParams[$param] = $row[ $param ];
                                        }

                                        //Old way, may be missing something in new implementation
                                        $args = array( $singleTable['header'][ $key ]['link']['routeid'] );
                                        $args = array_merge( $args, $params );
//                                                $editLink = call_user_func_array(array($url,'make'), $args);


                                        $routeId = $singleTable['header'][$key]['link']['routeid'];
                                        $editLink = app::get()->getRouter()->generate($routeId, $rowParams);

                                        if (!empty($singleTable['header'][ $key ]['link']['target']))
                                            $aTarget = 'target="'.$singleTable['header'][ $key ]['link']['target'].'"';

                                        echo '<a '.$aTarget.' href="'.$editLink.'" title="'.$singleTable['header'][ $key ]['link']['name'].'">';
                                    }

                                    if ( !empty( $singleTable['map'][ $key ] ) || !empty( $singleTable['header'][ $key ]['map'] ) )
                                    {

                                        if ( !empty( $singleTable['map'][ $key ] ))
                                            $dataMaps = explode('|', $singleTable['map'][ $key ]);
                                        else
                                            $dataMaps = explode('|', $singleTable['header'][ $key ]['map']);

                                        $cellData = '';

                                        foreach($dataMaps as $map)
                                        {
                                            $splitData = $row;
                                            $mapParts = explode('.', $map);

                                            if ( is_array($row) && count($mapParts)>1 ) {

                                                foreach ($mapParts as $part) {

                                                    if (is_array($splitData)) {
                                                        $splitData = $splitData[$part];
                                                    }
                                                    else {
                                                        $cellData .= $map;
                                                        break;
                                                    }

                                                }


                                                $cellData .= ' '.$splitData;
                                            }
                                            elseif (is_array($row) && array_key_exists($map, $row)) {
                                                $cellData .= ' '.$row[$map];
                                            }
                                            else {
                                                $cellData .= $map;
                                            }

                                        }

                                        $cellData = trim($cellData);

                                        if (isset($column['type'])) {
                                            $cellData = \sacore\utilities\stringUtils::typeFormat($cellData, $column['type']);
                                        }

                                        echo $cellData;

                                    }
                                    else
                                    {
                                        $cellData = $row[$key];

                                        if (isset($column['type'])) {
                                            $cellData = \sacore\utilities\stringUtils::typeFormat($cellData, $column['type']);
                                        }

                                        echo $cellData;
                                    }

                                    if ((!empty($singleTable['actions']['edit']) || !empty($singleTable['header'][ $key ]['link'])) && $enableActions)
                                        echo '</a> ';

                                    echo '</td>';
                                }

                                // PRINT DATA
                                if ( isset($singleTable['actions']) && !$enableActions ) {
                                    echo '<td  class="text-right"></td>';
                                }
                                elseif ( isset($singleTable['actions']) && $enableActions )
                                {
                                    echo '<td  class="text-right" nowrap>';
                                    foreach($singleTable['actions'] as $key=>$action)
                                    {
                                        $params = $singleTable['actions'][$key]['params'];

                                        if (!is_array($params))
                                            $params = array();

                                        $rowParams = [];
                                        foreach($params as $paramKey=>$param)
                                        {
                                            $splitData = $row;
                                            $mapParts = explode('.', $param);

                                            if ( is_array($row) && count($mapParts)>1 ) {

                                                foreach ($mapParts as $part) {
                                                    if (is_array($splitData)) {
                                                        $splitData = $splitData[$part];
                                                    }
                                                }
                                                $rowParams[$param] = $splitData;
                                            }
                                            elseif (is_array($row) && array_key_exists($param, $row)) {
                                                $rowParams[$param] = $row[$param];
                                            }
                                        }

                                        //Old way, could be missing something in current link implementation
                                        $args = array( $singleTable['actions'][$key]['routeid'] );
                                        $args = array_merge( $args, $params );
                                        $aLink = app::get()->getRouter()->generate($singleTable['actions'][$key]['routeid'], $rowParams);

                                        if (empty($aLink))
                                            $aLink = $singleTable['actions'][$key]['routeid'];

                                        if (!empty($action['querystring']))
                                            $aLink .= '?'.$action['querystring'];

                                        if (!empty($action['target']))
                                            $aTarget = 'target="'.$action['target'].'"';

                                        $data = '';
                                        if (!empty($action['data']) && is_array($action['data']))
                                        {
                                            foreach($action['data'] as $dataKey=>$value)
                                            {
                                                if (array_key_exists($value, $row))
                                                    $value = $row[ $value ];

                                                $data .= 'data-'.$dataKey.'="'.$value.'" ';
                                            }
                                        }

                                        if (!empty($action['icon']))
                                            echo '<a '.$aTarget.' href="'.$aLink.'" title="'.$action['name'].'" '.$data.' class="btn btn-xs btn-primary '.$action['class'].'">
																<i class="fa fa-'.$action['icon'].' bigger-120"></i>
																'.($action['showText'] ? $action['name'] : '').'
															</a> ';
                                        elseif ($key=='delete')
                                            echo '<a '.$aTarget.' href="'.$aLink.'"  title="'.$action['name'].'" '.$data.' class="confirm btn btn-xs btn-danger '.$action['class'].'">
																<i class="fa fa-trash-o bigger-120"></i>
															</a> ';
                                        elseif ($key=='edit')
                                            echo '<a '.$aTarget.' href="'.$aLink.'" title="'.$action['name'].'" '.$data.' class="btn btn-xs btn-info '.$action['class'].'">
																<i class="fa fa-edit bigger-120"></i>
															</a> ';
                                        else
                                            echo '<a href="'.$aLink.'" title="'.$action['name'].'" '.$data.' class="btn btn-xs btn-info  '.$action['class'].'">'.$action['name'].'</a> ';

                                    }
                                    echo '</td>';
                                }

                                echo '</tr>';
                            }
                        }
                        else
                        {
                            echo '<tr><td colspan="20">'.$singleTable['noDataMessage'].'</td></tr>';
                        }

                        ?>
                        </tbody>
                    </table>

                    <?php
                    if (isset($singleTable['currentPage']) && isset($singleTable['perPage']) && isset($singleTable['totalRecords']) && $singleTable['totalRecords']>0 )
                    {
                        ?>
                        <div class="row">

                            <div class="col-sm-6">
                                <?php
                                $maxRange = $singleTable['currentPage']*$singleTable['perPage'];
                                $maxRange = $maxRange > $singleTable['totalRecords'] ? $singleTable['totalRecords'] : $maxRange;
                                ?>

                                <?php

                                if (isset($singleTable['massActions']) && is_array($singleTable['massActions'])) {
                                    ?>
                                    <select class="perform-mass-action" data-key="<?=$tableKey?>" style="width: 200px" class="form-control">

                                        <option value="">-- MASS ACTIONS --</option>

                                        <?php
                                        foreach($singleTable['massActions'] as $action) {

                                            $params = $action['params'];

                                            if (!is_array($params))
                                                $params = array();

                                            foreach($params as $paramKey=>$param)
                                            {
                                                $splitData = $row;
                                                $mapParts = explode('.', $param);

                                                if ( is_array($row) && count($mapParts)>1 ) {

                                                    foreach ($mapParts as $part) {

                                                        if (is_array($splitData)) {
                                                            $splitData = $splitData[$part];
                                                        }
                                                    }

                                                    $params[$paramKey] = $splitData;
                                                }
                                                elseif (is_array($row) && $param && array_key_exists($param, $row)) {
                                                    $params[$paramKey] = $row[$param];
                                                }
                                            }

                                            $args = array( $action['routeid'] );
                                            $args = array_merge( $args, $params );
                                            $aLink = app::get()->getRouter()->generate($routeId, $params);

                                            ?>
                                            <option value="<?=$aLink?>" data-confirm="<?=$action['confirm'] ? 'true' : 'false'?>"><?=$action['name']?></option>
                                            <?php

                                        }
                                        ?>

                                    </select>
                                    <?php
                                }
                                ?>

                                <div class="dataTables_info" id="sample-table-2_info">Showing <?=($singleTable['currentPage']*$singleTable['perPage']) - $singleTable['perPage'] + 1?> to <?=$maxRange?> of <?=$singleTable['totalRecords']?> entries</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="dataTables_paginate paging_bootstrap">
                                    <ul class="pagination">
                                        <?php
                                        //											$link = \sacore\utilities\url::route();
                                        //May not be up to 100% functionality
                                        $array = explode('?',$_SERVER['REQUEST_URI']);
                                        $link = $array[0];
                                        $qsParts = $_GET;
                                        $parameters = explode('&', $array[1]);
                                        foreach($parameters as $query){
                                            [$key, $value] = explode('=', $query);
                                            $qsParts[$key] = $value;
                                            $qsPartsPerSelector[$key] = $value;
                                        }

                                        $qsPartsPerSelector = $_GET;

                                        if (is_array($singleTable['perPageSelection'])) {
                                            ?>
                                            <li class="pull-left">
                                                <label>Show: </label>
                                                <select id="table-per-page-show-<?= $tableKey ?>">
                                                    <?php
                                                    foreach ($singleTable['perPageSelection'] as $sel) {

                                                        $qsPartsPerSelector['page'] = 1;
                                                        $qsPartsPerSelector['limit'] = $sel;
                                                        $qs = http_build_query($qsPartsPerSelector);

                                                        ?>
                                                        <option value="<?= $link ?>?<?= $qs ?>" <?= $_GET['limit'] == $sel ? 'selected="selected"' : '' ?>><?= $sel ?></option>
                                                        <?php
                                                    }

                                                    $qsPartsPerSelector['page'] = 1;
                                                    $qsPartsPerSelector['limit'] = 999999;
                                                    $qs = http_build_query($qsPartsPerSelector);

                                                    ?>
                                                    <option value="<?= $link ?>?<?= $qs ?>" <?= $_GET['limit'] == '999999' ? 'selected="selected"' : '' ?>>All</option>
                                                </select>
                                                <script>
                                                    $('#table-per-page-show-<?=$tableKey?>').change(function () {
                                                        window.location.href = $(this).val();
                                                    });
                                                </script>
                                            </li>
                                            <?php
                                        }

                                        if ($singleTable['currentPage']>1)
                                        {
                                            $qsParts['page'] = $singleTable['currentPage']-1;
                                            $qs = http_build_query($qsParts);
                                            ?>
                                            <li class="prev"><a href="<?=$link?>?<?=$qs?>"><i class="fa fa-angle-double-left"></i></a></li>
                                            <?php
                                        }

                                        $firstPageToShow = $singleTable['currentPage'] - 3;
                                        if ($firstPageToShow<1) $firstPageToShow = 1;

                                        $lastPageToShow = $singleTable['currentPage'] + 3;
                                        if ($lastPageToShow>$singleTable['totalPages']) $lastPageToShow = $singleTable['totalPages'];

                                        if ( ($lastPageToShow - $firstPageToShow) < 6 )
                                        {
                                            $addToEnd = 6 - ($lastPageToShow - $firstPageToShow);
                                            $lastPageToShow += $addToEnd;
                                            if ($lastPageToShow>$singleTable['totalPages']) $lastPageToShow = $singleTable['totalPages'];
                                        }

                                        if ( ($lastPageToShow - $firstPageToShow) < 6 )
                                        {
                                            $addToEnd =  6 - ($lastPageToShow - $firstPageToShow);
                                            $firstPageToShow -= $addToEnd;
                                            if ($firstPageToShow<1) $firstPageToShow = 1;
                                        }

                                        if ($firstPageToShow!=1) {
                                            $qsParts['page'] = 1;
                                            $qs = http_build_query($qsParts);
                                            ?>
                                            <li class="<?= (1 == $singleTable['currentPage'] ? 'active' : '') ?>"><a href="<?= $link ?>?<?= $qs ?>">1</a></li>
                                            <li><a href="#">...</a></li>
                                            <?php
                                        }

                                        for($i=$firstPageToShow; $i<=$lastPageToShow; $i++)
                                        {
                                            $qsParts['page'] = $i;
                                            $qs = http_build_query($qsParts);
                                            ?>
                                            <li class="<?=($i==$singleTable['currentPage'] ? 'active' : '')?>"><a href="<?=$link?>?<?=$qs?>"><?=$i?></a></li>
                                            <?php
                                        }

                                        if ($lastPageToShow!=$singleTable['totalPages']) {
                                            $qsParts['page'] = $singleTable['totalPages'];
                                            $qs = http_build_query($qsParts);
                                            ?>
                                            <li><a href="#">...</a></li>
                                            <li class="<?= ($singleTable['totalPages'] == $singleTable['currentPage'] ? 'active' : '') ?>"><a href="<?= $link ?>?<?= $qs ?>"><?=$singleTable['totalPages']?></a></li>
                                            <?php
                                        }

                                        if ($singleTable['currentPage']!=$singleTable['totalPages'])
                                        {
                                            $qsParts['page'] = $singleTable['currentPage']+1;
                                            $qs = http_build_query($qsParts);
                                            ?>
                                            <li class="next"><a href="<?=$link?>?<?=$qs?>"><i class="fa fa-angle-double-right"></i></a></li>
                                            <?php
                                        }

                                        if ($singleTable['totalPages']>1 && $singleTable['showAllBtn'])
                                        {
                                            $qsParts['page'] = 1;
                                            $qsParts['limit'] = 999999;
                                            $qs = http_build_query($qsParts);
                                            ?>
                                            <li class="next"><a href="<?=$link?>?<?=$qs?>">All</a></li>
                                            <?php
                                        }
                                        ?>
                                    </ul>

                                </div>

                            </div>
                        </div>
                    <?php } ?>

                </div><!-- /.table-responsive -->
            </div><!-- /span -->
        </div><!-- /row -->
        <?php
    }
}

if (isset($singleTable['massActions']) && is_array($singleTable['massActions'])) {
    ?>

    <script>

        $(document).ready( function() {

            $('.mass-select-all').click( function() {
                var key = $(this).data('key');
                if ($(this).is(':checked')) {
                    $('.mass-select[data-key="'+key+'"]').prop('checked', true);
                }
                else
                {
                    $('.mass-select[data-key="'+key+'"]').prop('checked', false);
                }
            })

            $('.mass-select').click( function() {
                var key = $(this).data('key');
                $('#mass-select-all-'+key).prop('checked', false);
            })

            $('.perform-mass-action').change( function() {

                //submitMassActions( $(this).val() , $(this).data('key') );Z

                var selection = $(this).val();
                var key = $(this).data('key');


                if ( $('option:selected', $(this)).data('confirm')==true ) {

                    bootbox.dialog({
                        message: "<span class='bigger-110'>Are you sure?</span>",
                        buttons:
                            {
                                "OK" :
                                    {
                                        "label" : "<i class='icon-ok'></i> OK",
                                        "className" : "btn-sm btn-info",
                                        "callback": function() {
                                            submitMassActions(selection, key)
                                        }
                                    },
                                "Cancel" :
                                    {
                                        "label" : "<i class='icon-times'></i> Cancel",
                                        "className" : "btn-sm btn-danger",
                                        "callback": function() { }
                                    }

                            }
                    });

                }
                else
                {
                    submitMassActions(selection, key);
                }

            })
        })

        function submitMassActions(selection, key) {
            if (selection=='')
                return;

            var form = $('#mass-action-form');
            form.empty();
            form.attr('action', selection );


            $('.mass-select[data-key="'+key+'"]:checked').each( function() {
                form.append('<input type="hidden" name="massaction[]" value="'+$(this).val()+'" />');
            })

            form.submit();
        }

    </script>

    <form id="mass-action-form" method="post">


    </form>

    <?php
}
?>
@show
