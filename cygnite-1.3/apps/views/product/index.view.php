<?php
use Cygnite\AssetManager\Asset;
use Cygnite\Common\UrlManager\Url;

if ($this->hasFlash('success')) {
    echo $this->getFlash('success');
} elseif ($this->hasError()) {
    echo $this->getFlash('error');
}
?>
<div class="page-header">
	<h3>CRUD Application
        <div class="pull-right">
        <!--<span class="glyphicon glyphicon-plus-sign">-->
        <?php echo Asset::link('product/add', 'Add Product', array('class' => 'btn btn-small btn-info cboxElement')); ?>
        </div>
	</h3>
</div>

<table  id="dataGrid" class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Sl No.</th>
            			<th>Product Name</th>
			<th>Category</th>
			<th>Description</th>
			<th>Validity</th>
			<th>Price</th>
			<th>Created At</th>
			<th>Updated At</th>


            <th class="sorter-false">Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if (count($this->records) > 0) {
        //$i = 1;
        if (Url::segment(2) == '' || Url::segment(3) == '' || Url::segment(3) == 1 ) {
            $i =1;
        } else {
            $i = (Url::segment(3) - 1) * $this->records[0]->perPage + 1;
        }

        $rowType = null;
        foreach ($this->records as $key => $value) {

            $rowType = ($i % 2 == 0) ? 'even' : 'odd';
    ?>
        <tr class='<?php echo $rowType; ?>'>
            <td> <?php echo $i; ?></td>
            			<td><?php echo $value->product_name; ?></td>
			<td><?php echo $value->category; ?></td>
			<td><?php echo $value->description; ?></td>
			<td><?php echo $value->validity; ?></td>
			<td><?php echo $value->price; ?></td>
			<td><?php echo $value->created_at; ?></td>
			<td><?php echo $value->updated_at; ?></td>


            <td>
                <?php
                echo Asset::link('product/show/' . $value->id, 'View', array('class' => 'btn btn btn-info btn-xs'));
                echo Asset::link('product/edit/' . $value->id, 'Edit', array('class' => 'btn btn-default btn-xs'));
                echo Asset::link('product/delete/' . $value->id, 'Delete', array('class' => 'btn btn-danger btn-xs' ));
                ?>

            </td>
        </tr>
    <?php $i++;
        }
    } else {
        echo 'No records found !';
    }
    ?>
    </tbody>

</table>

<nav > <?php //echo $this->links; ?> </nav>