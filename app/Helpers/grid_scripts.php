<?php
include "autocomplete_grid.php";
echo $autogrid_html;
?>
<script language="javascript" type="text/javascript">
    function after_complete_<?= $grid_id ?>(rowid, cellname) {
        switch (cellname) {
            case 'bank_name':
                if (<?= $grid_id ?>_selected_suggest) {
                    // untuk assign ke kolom mana aja
                    <?php echo grid_selected_suggest($grid_id, [array('field' => 'id', 'input' => 'bank_id'), array('field' => 'name', 'input' => 'bank_name')]) ?>
                }
                break;
            case 'item_code':
                if (<?= $grid_id ?>_selected_suggest) {
                    // untuk assign ke kolom mana aja
                    <?php echo grid_selected_suggest($grid_id, [array('field' => 'id', 'input' => 'item_id'), array('field' => 'name', 'input' => 'item_name'), array('field' => 'unit_name', 'input' => 'unit'), array('field' => 'buy_price', 'input' => 'price')]) ?>
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

        if (grid_id == 'transaction_purchase_item') {
            setSubtotalPerItem(rowid);
            setSubtotal();
        }

    }

    function after_save_cell_<?= $grid_id ?>(rowid, cellname) {
        // NO FUNCTION
        var grid_id = '<?= $grid_id ?>';

        if (grid_id == 'transaction_purchase_item') {
            setSubtotalPerItem(rowid);
            setSubtotal();
        }

    }

    function after_delete_<?= $grid_id ?>() {
        // NO FUNCTION
        var grid_id = '<?= $grid_id ?>';

        if (grid_id == 'transaction_purchase_item') {
            setSubtotalPerItem(rowid);
            setSubtotal();
        }

    }

    function row_validation_<?= $grid_id ?>(rowid) {
        return true;
    }
</script>