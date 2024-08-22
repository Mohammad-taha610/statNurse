@extends('master')
@section('site-container')

<a class="btn" href="@url('sa_developer_doctrine_entities')?c=">Generate/Update Entities</a>
<a class="btn" href="@url('sa_developer_doctrine_execute')?c=orm_repositories">Generate/Update Repositories</a>
<a class="btn" href="@url('sa_developer_doctrine_execute')?c=orm_proxies">Generate Proxies</a>
<a class="btn" href="@url('sa_developer_doctrine_execute')?c=orm_schema_update">Schema Tool Update (!Caution!)</a>
<a class="btn" href="@url('sa_developer_doctrine_execute')?c=migration_status">Migration Status</a>
<a class="btn" href="@url('sa_developer_doctrine_execute')?c=migration_migrate">Migrate</a>
<a class="btn" href="@url('sa_developer_doctrine_execute')?c=migration_migrate_dry">Migrate Dry-Run</a>
<a class="btn" href="@url('sa_developer_doctrine_execute')?c=migration_migrate_generate">Migration Generate</a>

<pre style="margin-top: 25px"><?=! empty($output) ? $output : 'Please select an action.'?></pre>

@show