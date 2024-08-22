
<?php
if (is_array($table))
{
	foreach( $table as $singleTable )
	{
        if (isset($viewParams[0]))
        {
            if ($tableKey!=$viewParams[0])
            {
                continue;
            }
        }

        $id = $singleTable['id'] ? $singleTable['id'].'_' : '';

        if (empty($singleTable['noDataMessage']))
            $singleTable['noDataMessage'] = 'No data is currently available.';

        if (!isset($singleTable['sortable']))
            $singleTable['sortable'] = true;

		?>
		<div id="<?=$singleTable['tabid']?>" class="tab-pane">
			<div class="space-10"></div>
			<h4 class="header blue bolder smaller"><?=$singleTable['title']?></h4>
            <div class="row">
            <div class="col-xs-12">

            <?php
            if (  $singleTable['searchable'] )
            {
                ?>
                <div id="search_<?=$tableKey?>" class="<?= $_GET['action']=='search' || $singleTable['showSearchOpen']  ? '' : 'saHidden' ?>">
                    <div class="well">
							<span class="blue font20">
								Search
							</span>
                        <hr>
                        <form method="get">
                            <input type="hidden" value="search" name="action">
                            <div>
                                <?php
                                foreach( $singleTable['header'] as $key=>$column )
                                {
                                    $columnName = $column;
                                    $class = '';
                                    $searchable = true;
                                    if (is_array($column))
                                    {
                                        $columnName = $column['name'];
                                        $class = $column['class'];
                                        $searchable = isset($column['searchable']) ? $column['searchable'] : true;
                                    }

                                    if (!$searchable) continue;


                                    if ( (!empty( $singleTable['header'][ $key ]['map'] ) || !empty( $singleTable['map'][ $key ] ) || !empty( $singleTable['header'][ $key ]['sort']  )) && $singleTable['sortable'] ) {
                                        if (!empty($singleTable['header'][$key]['sort']))
                                            $dbField = $singleTable['header'][$key]['sort'];
                                        elseif (!empty($singleTable['header'][$key]['map']))
                                            $dbField = $singleTable['header'][$key]['map'];
                                        elseif (!empty($singleTable['map'][$key]))
                                            $dbField = $singleTable['map'][$key];
                                    }

                                    $dbField = str_replace('.', '!', $dbField);

                                    ?>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-2 control-label no-padding-right" for="form-field-to"><?=$columnName ?>: </label>
                                            <div class="col-sm-10">
                                                <input class="col-xs-12 col-sm-10" type="text" name="q_<?=$dbField ?>" value="<?=$_GET['q_'.$dbField]?>" />
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit">
                                        <i class="fa fa-ok bigger-110"></i>
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
            <table id="sample-table-1" class="table table-striped table-bordered table-hover" style="margin-bottom: 0px">
            <thead>
            <tr>

                <?php
                foreach( $singleTable['header'] as $key=>$column )
                {
                    $columnName = $column;
                    $class = '';
                    if (is_array($column))
                    {
                        $columnName = $column['name'];
                        $class = $column['class'];
                    }

                    if ( (!empty( $singleTable['header'][ $key ]['map'] ) || !empty( $singleTable['map'][ $key ] ) || !empty( $singleTable['header'][ $key ]['sort']  )) && $singleTable['sortable'] )
                    {
                        $caret = '<i class="fa fa-sort pull-right"></i>';

                        if (!empty( $singleTable['header'][ $key ]['sort'] ))
                            $dbField = $singleTable['header'][ $key ]['sort'];
                        elseif (!empty( $singleTable['header'][ $key ]['map'] ))
                            $dbField = $singleTable['header'][ $key ]['map'];
                        elseif (!empty( $singleTable['map'][ $key ] ))
                            $dbField = $singleTable['map'][ $key ];

                        $sortDir = 'ASC';
                        if ($dbField==$_REQUEST[$id.'sort'])
                        {
                            $caret = '<i class="fa fa-caret-up pull-right"></i>';

                            if ($_REQUEST[$id.'sortDir'] == 'ASC')
                            {
                                $caret = '<i class="fa fa-caret-down pull-right"></i>';
                                $sortDir = 'DESC';
                            }
                        }
                    }

                    $qsParts = $_GET;
                    $qsParts[$id.'sort'] = $dbField;
                    $qsParts[$id.'sortDir'] = $sortDir;
                    $qs = http_build_query($qsParts);

                    if ($singleTable['sortable'])
                    {
                        ?>
                        <th class="<?=$class?>">
                            <a class="" href="?<?=$qs?><?=$singleTable['append_links']?>"> <?=$columnName?></a>
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

                        $args = array( $singleTable['headerActions'][$key]['routeid'] );
                        $args = array_merge( $args, $params );
                        $aLink = call_user_func_array(array($url,'make'), $args);

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

                        if (!empty($action['querystring']))
                            $aLink .= '?'.$action['querystring'];

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
                    if (  $singleTable['searchable'] ) {
                        ?>
                        <button class="btn btn-xs btn-primary" onclick="$('#search_<?=$tableKey?>').slideToggle('50')">Search</button>
                    <?php
                    }

                    if (  isset($singleTable['tableCreateRoute']) )
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

                        $link = call_user_func_array(array($url,'make'), $args);

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
                        $row = $row->toArray(true);
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

                            foreach($params as $paramKey=>$param)
                            {
                                if (array_key_exists($param, $row))
                                    $params[$paramKey] = $row[ $param ];
                            }

                            $args = array( $singleTable['actions']['edit']['routeid'] );
                            $args = array_merge( $args, $params );
                            $editLink = call_user_func_array(array($url,'make'), $args);

                            if (!empty($singleTable['actions']['edit']['target']))
                                $aTarget = 'target="'.$singleTable['actions']['edit']['target'].'"';

                            echo '<a '.$aTarget.' href="'.$editLink.'" title="'.$singleTable['actions']['edit']['name'].'">';
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
                                if (is_array($row) && array_key_exists($map, $row))
                                    $cellData .= ' '.$row[$map];
                                else
                                    $cellData .= $map;
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

                        if (!empty($singleTable['actions']['edit']) && $enableActions)
                            echo '</a> ';

                        echo '</td>';
                    }

                    // PRINT DATA
                    if ( isset($singleTable['actions']) && !$enableActions ) {
                        echo '<td  class="text-right"></td>';
                    }
                    elseif ( isset($singleTable['actions']) && $enableActions )
                    {
                        echo '<td  class="text-right">';
                        foreach($singleTable['actions'] as $key=>$action)
                        {
                            $params = $singleTable['actions'][$key]['params'];

                            if (!is_array($params))
                                $params = array();

                            foreach($params as $paramKey=>$param)
                            {
                                if (is_array($row) && array_key_exists($param, $row))
                                    $params[$paramKey] = $row[ $param ];
                            }

                            $args = array( $singleTable['actions'][$key]['routeid'] );
                            $args = array_merge( $args, $params );
                            $aLink = call_user_func_array(array($url,'make'), $args);

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
                                echo '<a href="'.$aLink.'" title="'.$action['name'].'" '.$data.' class="btn btn-xs btn-info  '.$action['class'].'">'.$action['name'].'</a>';

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
                        <div class="dataTables_info" id="sample-table-2_info">Showing <?=($singleTable['currentPage']*$singleTable['perPage']) - $singleTable['perPage'] + 1?> to <?=$maxRange?> of <?=$singleTable['totalRecords']?> entries</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="dataTables_paginate paging_bootstrap">
                            <ul class="pagination">
                                <?php
                                $link = explode("/",$_SERVER['REQUEST_URI'],2)[1];
                                //Old way
//                                $link = \sacore\utilities\url::route();
                                $qsParts = $_GET;

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
                                    $qsParts[$id.'page'] = 1;
                                    $qs = http_build_query($qsParts);
                                    ?>
                                    <li class="<?= (1 == $singleTable['currentPage'] ? 'active' : '') ?>"><a href="<?= $link ?>?<?= $qs ?><?=$singleTable['append_links']?>">1</a></li>
                                    <li><a href="#">...</a></li>
                                <?php
                                }

                                for($i=$firstPageToShow; $i<=$lastPageToShow; $i++)
                                {
                                    $qsParts[$id.'page'] = $i;
                                    $qs = http_build_query($qsParts);
                                    ?>
                                    <li class="<?=($i==$singleTable['currentPage'] ? 'active' : '')?>"><a href="<?=$link?>?<?=$qs?><?=$singleTable['append_links']?>"><?=$i?></a></li>
                                <?php
                                }

                                if ($lastPageToShow!=$singleTable['totalPages']) {
                                    $qsParts[$id.'page'] = $singleTable['totalPages'];
                                    $qs = http_build_query($qsParts);
                                    ?>
                                    <li><a href="#">...</a></li>
                                    <li class="<?= ($singleTable['totalPages'] == $singleTable['currentPage'] ? 'active' : '') ?>"><a href="<?= $link ?>?<?= $qs ?><?=$singleTable['append_links']?>"><?=$singleTable['totalPages']?></a></li>
                                <?php
                                }

                                if ($singleTable['currentPage']!=$singleTable['totalPages'])
                                {
                                    $qsParts[$id.'page'] = $singleTable['currentPage']+1;
                                    $qs = http_build_query($qsParts);
                                    ?>
                                    <li class="next"><a href="<?=$link?>?<?=$qs?><?=$singleTable['append_links']?>"><i class="fa fa-angle-double-right"></i></a></li>
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
		</div>
		<?php
	}
}
?>