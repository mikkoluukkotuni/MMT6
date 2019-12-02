
<div class="metrictypes view large-7 medium-14 columns content float: left">
    <h3><?= h($metrictype->id) ?></h3>
    <button id="navbutton"><?= $this->Html->link(__('Edit Metrictype'), ['action' => 'edit', $metrictype->id]) ?></button>
    <table class="vertical-table">
        <tr>
            <th><?= __('Description') ?></th>
            <td><?= h($metrictype->description) ?></td>
        </tr>
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= $this->Number->format($metrictype->id) ?></td>
        </tr>
    </table>
</div>
