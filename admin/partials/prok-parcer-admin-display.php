<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    prok_parcer
 * @subpackage prok_parcer/admin/partials
 */
//obj содержит поля `ID`,`status`,`name`,`title_preg`,`index_url`,`prok_begin_index`,`prok_end_index`,`prok_begin`,`prok_end`,`ingr_pr`,`step_pr`,`autoupdate`,`urls`,`count_add_post`,`first_number`,`category`,`offset`
//function lent_from($url,$prok_begin_index,$prok_end_index,$prok_begin,$prok_end,$id,$name,$title,$ingr_pr,$step_pr,$autoupdate,$countAddPost,$firstNumber,$cat){
function prokDisplayLentForm($obj){
    ?>
    <style>
        .updated{
            display: none;
        }
    </style>
    <!-- 	<div class="updated"><p><strong><?php _e('Options saved.', 'mt_trans_domain' ); ?></strong></p></div> -->
    <div class="wrap">
        <h2>Настройки</h2>
        <div class="container">
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span >Наименование ленты</span>
                </div>
                <input id="name" type="text" name="" value="<?php echo $obj->name; ?>" size="100">
            </div>
            <br>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span >URL индексной страницы</span>
                </div>
                <input id="url" type="text" name="" value="<?php echo $obj->index_url; ?>" size="70">
            </div>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span >Начальная точка индексной страницы</span>
                </div>
                <input id="one" type="text" name="" value="<?php echo htmlentities( $obj->prok_begin_index); ?>" size="120">
            </div>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span  class="label-input">Конечная точка индексной страницы</span>
                </div>
                <input id="two" type="text" name="" value="<?php echo htmlentities( $obj->prok_end_index); ?>" size="120">
            </div>
            <br>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span >Заголовок</span>
                </div>
                <input id="title" type="text" name="" value="<?php echo htmlentities( $obj->title_preg); ?>" size="120">
            </div>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span >Начальная точка страницы</span>
                </div>
                <input id="oneContent" type="text" name="" value="<?php echo htmlentities( $obj->prok_begin); ?>" size="120">
            </div>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span  class="label-input">Конечная точка страницы</span>
                </div>
                <input id="twoContent" type="text" name="" value="<?php echo htmlentities( $obj->prok_end); ?>" size="120">
            </div>
            <br>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span  class="label-input">шаблон для поиска ингридиентов</span>
                </div>
                <input id="prIng" type="text" name="" value="<?php echo htmlentities( $obj->ingr_pr); ?>" size="120">
            </div>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span  class="label-input">шаблон для поиска шагов</span>
                </div>
                <input id="prStep" type="text" name="" value="<?php echo htmlentities( $obj->step_pr); ?>" size="120">
            </div>
            <br>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span  class="label-input">Время автообновления</span>
                </div>
                <input id="timeAutoupdate" type="text" name="" value="<?php echo $obj->autoupdate; ?>" size="120">
            </div>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span  class="label-input">Кол-во записей за один прогон</span>
                </div>
                <input id="countAddPost" type="text" name="" value="<?php echo $obj->count_add_post; ?>" size="120">
            </div>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span  class="label-input">Номер первой записей(0-с первой)</span>
                </div>
                <input id="firstNumber" type="text" name="" value="<?php echo $obj->first_number; ?>" size="120">
            </div>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span  class="label-input">Указать рубрику</span>
                </div>
                <?php $cat = $obj->category;  wp_dropdown_categories("show_count=1&hierarchical=1&selected=$cat"); ?>
            </div>
            <?php                 prokDisplayStatus((bool)$obj->status);            ?>
        </div>
        <br>
        <?php    prokDisplayProcesses($obj->ID);  ?>
        <button id="dddd" class="button" onclick="getHr2(<?php echo $obj->ID; ?>,1)">OK</button>
        <button id="dddd2" class="button" onclick="getHr2(<?php echo $obj->ID; ?>,0)">TEST</button>
        <button id="save" class="button" onclick="saveOptions(<?php echo $obj->ID; ?>)">SAVE</button>
    </div>
    <div id="responseHref"></div>
    <div id="response"></div>
    <?php

}

function prokDisplayStatus($status){
    ?>
                <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span  class="label-input">Включить ленту</span>
                </div>
                <label class="switch">
                    <input id="st" type="checkbox" <?php if($status) { echo "checked"; } ?>>
                    <div>
                        <span></span>
                    </div>
                </label>
            </div>
    <?php
}

function prokDisplayRowProcess($row, $i){
    ?>
    <tr align="center">
        <td><?php echo $i; ?></td>
        <td>
            <select name="params[usrepl][<?php echo $i; ?>][type]" style="width:150px;">
                <?php
                prokOptionSelector($row->status);
                ?>
            </select>
        </td>
        <td>
            <input size="30" type="text" name="params[usrepl][<?php echo $i; ?>][name]" value="">
        </td>
        <td>
            <input size="60" type="text" name="params[usrepl][<?php echo $i; ?>][search]" placeholder="Введите регулярные выражения для поиска" value="<?php echo htmlentities($row->value); ?>">
        </td>
        <td>
            <input size="50" type="text" name="params[usrepl][<?php echo $i; ?>][replace]" placeholder="Введите шаблон замены, если нужно" value="<?php echo htmlentities($row->replacement); ?>">
        </td>
        <td>
            <input style="text-align:center;" size="5" type="text" name="params[usrepl][<?php echo $i; ?>][limit]" value="">
        </td>
    </tr>
    <?php
}

function prokOptionSelector($val){
    $def="";
    $index ="";
    $page="";
    $title="";
    $text="";
    if($val == '0'){
        $def="selected";
    }else if($val == 'index'){
        $index="selected";
    }else if($val == 'page'){
        $page="selected";
    }else if($val == 'title'){
        $title="selected";
    }else if($val == 'text'){
        $text="selected";
    }
    ?>
    <option <?php echo $def; ?> value="0">выключен</option>
    <option <?php echo $index; ?> value="index">индексная html-страница (rss-контент или vk-лента)</option>
    <option <?php echo $page; ?> value="page">страница контента до парсинга</option>
    <!-- 	<option value="intro">анонс</option> -->
    <!-- 	<option <?php echo $text; ?> value="text">полный текст</option> -->
    <!-- 	<option <?php echo $title; ?> value="title">заголовок</option> -->
    <?php
}

function createMainTable(){
    global $wpdb;
    ?>
    <button onclick="document.location='admin.php?page=prok&prk-action=add'">Добавить ленту</button>
    <button onclick="del()">Удалить выделенные ленты</button>

    <table class="wp-list-table widefat fixed striped table-view-list toplevel_page_wpgrabber-index">
        <thead>
        <tr>
            <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Выделить все</label><input id="cb-select-all-1" type="checkbox"></td>
            <th scope="col" id="name" class="manage-column column-name column-primary sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=name&amp;order=asc"><span>Наименование ленты</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" id="type" class="manage-column column-type sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=type&amp;order=asc"><span>Тип</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" id="url" class="manage-column column-url sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=url&amp;order=asc"><span>URL</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" id="published" class="manage-column column-published sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=published&amp;order=asc"><span>Статус</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" id="catid" class="manage-column column-catid sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=catid&amp;order=asc"><span>Рубрики</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" id="id" class="manage-column column-id sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=id&amp;order=asc"><span>ID</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" id="last_update" class="manage-column column-last_update sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=last_update&amp;order=asc"><span>Обновление</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" id="count_posts" class="manage-column column-count_posts sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=count_posts&amp;order=asc"><span>Кол-во записей</span><span class="sorting-indicator"></span></a></th>
        </tr>
        </thead>

        <tbody id="the-list">
        <?php

        $count = $wpdb->get_var( "SELECT COUNT(*) FROM prok_table" );
        for($i=0;$i<$count;$i++){
            $obj = $wpdb->get_row( 'SELECT * FROM prok_table', OBJECT, $i );
            createRow($obj);
        }

        ?>

        </tbody>

        <tfoot>
        <tr>
            <td class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-2">Выделить все</label><input id="cb-select-all-2" type="checkbox"></td>
            <th scope="col" class="manage-column column-name column-primary sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=name&amp;order=asc"><span>Наименование ленты</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" class="manage-column column-type sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=type&amp;order=asc"><span>Тип</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" class="manage-column column-url sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=url&amp;order=asc"><span>URL</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" class="manage-column column-published sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=published&amp;order=asc"><span>Статус</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" class="manage-column column-catid sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=catid&amp;order=asc"><span>Рубрики</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" class="manage-column column-id sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=id&amp;order=asc"><span>ID</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" class="manage-column column-last_update sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=last_update&amp;order=asc"><span>Обновление</span><span class="sorting-indicator"></span></a></th>
            <th scope="col" class="manage-column column-count_posts sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=count_posts&amp;order=asc"><span>Кол-во записей</span><span class="sorting-indicator"></span></a></th>
        </tr>
        </tfoot>

    </table>
    <?php
}

function createRow($row){
    ?>
    <tr>
        <th scope="row" class="check-column">
            <input type="checkbox" name="row" value="<?php echo $row->ID; ?>">
        </th>
        <td class="name column-name has-row-actions column-primary" data-colname="Наименование ленты"><?php echo $row->name; ?>
            <div class="row-actions"><span class="edit"><a href="?page=prok&prk-action=edit&prk-id=<?php echo $row->ID; ?>">Изменить</a></span>
                <!--                 <div class="row-actions"><span class="edit"><a href="?page=prok&prk-action=edit&prk-id=<?php echo $row->ID; ?>">Изменить</a> | </span><span class="test"><a href="?page=prok&amp;prk-action=list&amp;id=<?php echo $row->ID; ?>" onclick="wpgrabberRun(<?php echo $row->ID; ?>, true); return false;">Тест&nbsp;импорта</a> | </span>
                     <span
                         class="import"><a href="?page=prok&amp;prk-action=list&amp;id=<?php echo $row->ID; ?>" onclick="wpgrabberRun(<?php echo $row->ID; ?>, false); return false;">Импорт</a></span>-->
            </div><button type="button" class="toggle-row"></button><button type="button" class="toggle-row"></button></td>
        <td
                class="type column-type" data-colname="Тип">html</td>
        <td class="url column-url" data-colname="URL"><a target="_blank" href="<?php echo $row->index_url; ?>"><?php echo $row->index_url; ?></a></td>
        <td class="published column-published" data-colname="Статус"><a href="?page=wpgrabber-index&amp;rows[]=42&amp;action=Off"><span style="color:blue;"><?php echo echoBool($row->status); ?></span></a></td>
        <td class="catid column-catid" data-colname="Рубрики"><?php echo get_the_category_by_ID($row->category);?></td>
        <td class="id column-id" data-colname="ID"><?php echo $row->ID; ?></td>
        <td class="last_update column-last_update" data-colname="Обновление">0</td>
        <td class="count_posts column-count_posts" data-colname="Кол-во записей">0</td>
    </tr>
    <?php
}

function echoBool($bool){
    if($bool){
        return "Вкл. ";
    }
    return "Выкл. ";
}

function prokDisplayProcesses($id){
    ?>
    <td colspan="2">
    <fieldset style="width: 1000px;border: 1px solid;padding: 5px;">
        <legend>Дополнительные шаблоны обработки:</legend>
        <p><span style="color:red;"><a href="https://wpgrabber.ru.com/regexp.png" target="_blank"><b>Азбука регулярных выражений</b></a></span></p>
        <p>Примеры частого построения: [^&gt;]+ ; [\w-]+ ; [\w\d-_]{1,}</p>
        <div style="overflow: auto; height: 400px;">                                <style>
                .truser tr td, tr th {
                    padding: 3px;
                    background: #e7e7e7;
                    font-size: 12px;
                }

                .truser input, .truser select {
                    font-size: 12px;
                }

                .truser tr th {
                    text-align: center;
                }
            </style>
            <table class="truser">
                <tbody><tr>
                    <th width="30px">#</th>
                    <th>Объект применения</th>
                    <th>Наименование шаблона</th>
                    <th>Шаблон поиска</th>
                    <th>Шаблон замены</th>
                    <th>Кол-во замен</th>
                </tr>
                <?php
                for($i=0;$i<50;$i++){
                    $row = getProcessData($id,$i);
                    if(!is_null($row)){
                        prokDisplayRowProcess($row,$i);
                    }else{
                        prokDisplayRowProcess(new ProccesObj(),$i);
                        //prok_row_process_display(["","",""],$i);
                    }

                }

                ?>
                </tbody></table>
        </div>
    </fieldset>
    </td><?php
}

?>
