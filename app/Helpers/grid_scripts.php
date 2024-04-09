<?php
include "autocomplete_grid.php";
echo $autogrid_html;
?>
<script language="javascript" type="text/javascript">
    function after_complete_<?= $grid_id ?>(rowid, cellname) {
        switch (cellname) {
            case 'branch_name':
                if (<?= $grid_id ?>_selected_suggest) {
                    // untuk assign ke kolom mana aja
                    <?php echo grid_selected_suggest($grid_id, [array('field' => 'id', 'input' => 'branch_id')]) ?>
                }
                break;
            case 'user_name':
                if (<?= $grid_id ?>_selected_suggest) {
                    // untuk assign ke kolom mana aja
                    <?php echo grid_selected_suggest($grid_id, [array('field' => 'id', 'input' => 'user_id')]) ?>
                    <?php echo grid_selected_suggest($grid_id, [array('field' => 'email', 'input' => 'user_email')]) ?>
                }
                break;
            case 'user_group_name':
                if (<?= $grid_id ?>_selected_suggest) {
                    // untuk assign ke kolom mana aja
                    <?php echo grid_selected_suggest($grid_id, [array('field' => 'id', 'input' => 'user_group_id')]) ?>
                }
                break;
            case 'city_name':
                if (<?= $grid_id ?>_selected_suggest) {
                    // untuk assign ke kolom mana aja
                    <?php echo grid_selected_suggest($grid_id, [array('field' => 'id', 'input' => 'id')]) ?>
                }
                break;
            case 'bank_name':
                if (<?= $grid_id ?>_selected_suggest) {
                    // untuk assign ke kolom mana aja
                    <?php echo grid_selected_suggest($grid_id, [array('field' => 'id', 'input' => 'bank_id'), array('field' => 'name', 'input' => 'bank_name')]) ?>
                }
                break;
            case 'item_code':
                if (<?= $grid_id ?>_selected_suggest) {
                    // untuk assign ke kolom mana aja
                    <?php echo grid_selected_suggest($grid_id, [array('field' => 'id', 'input' => 'item_id'), array('field' => 'name', 'input' => 'item_name'), array('field' => 'unit1', 'input' => 'qty_unit'), array('field' => 'unit3', 'input' => 'total_unit'), array('field' => 'conversion2', 'input' => 'conversion2'), array('field' => 'conversion3', 'input' => 'conversion3'), array('field' => 'unit1', 'input' => 'unit1'), array('field' => 'unit2', 'input' => 'unit2'), array('field' => 'unit3', 'input' => 'unit3'), array('field' => 'unit3', 'input' => 'content_unit'), array('field' => 'content_price', 'input' => 'content_price')]) ?>
                }
                break;
            case 'item_name':
                if (<?= $grid_id ?>_selected_suggest) {
                    // untuk assign ke kolom mana aja
                    <?php echo grid_selected_suggest($grid_id, [array('field' => 'id', 'input' => 'item_id')]) ?>
                }
                break;
            case 'supplier':
                if (<?= $grid_id ?>_selected_suggest) {
                    // untuk assign ke kolom mana aja
                    <?php echo grid_selected_suggest($grid_id, [array('field' => 'id', 'input' => 'supplier_id')]) ?>
                }
                break;
            default:
                break;
        }

        <?= $grid_id ?>_selected_suggest = false;
    }

    function grid_complete_<?= $grid_id ?>(rowid, cellname) {
        // NO FUNCTION
        console.log(getAllRows_<?= $grid_id ?>());

        var grid_id = '<?= $grid_id ?>';

    }

    function after_save_cell_<?= $grid_id ?>(rowid, cellname) {
        // NO FUNCTION
        var grid_id = '<?= $grid_id ?>';

    }

    function after_delete_<?= $grid_id ?>() {
        // NO FUNCTION
        var grid_id = '<?= $grid_id ?>';

    }

    function row_validation_<?= $grid_id ?>(rowid) {
        // SETTING ROW VALIDATION
        // var cells = [
        // 	"nama_pegawai"
        // ];
        // var check_failed = checkValueCells_<?= $grid_id ?>(rowid,cells);
        // if(check_failed != ''){
        // 	return check_failed;
        // }else{
        return true;
        // }
        // END SETTING ROW VALIDATION
    }
</script>