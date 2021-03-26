<form class="vg-wrap vg-element vg-ninteen-of-twenty" method="post" action="/admin/shop/edit"
      enctype="multipart/form-data">
    <div class="vg-wrap vg-element vg-full">
        <div class="vg-wrap vg-element vg-full vg-firm-background-color4 vg-box-shadow">
            <div class="vg-element vg-half vg-left">
                <div class="vg-element vg-padding-in-px">
                    <input type="submit" class="vg-text vg-firm-color1 vg-firm-background-color4 vg-input vg-button" value="Сохранить">
                </div>
                <div class="vg-element vg-padding-in-px">
                    <a href=""
                       class="vg-text vg-firm-color1 vg-firm-background-color4 vg-input vg-button vg-center vg_delete">
                        <span>Удалить</span>
                    </a>
                </div>
            </div>
        </div>
    </div>


    <?php if($this->data): ?>
    <!-- атрибут input подключается только если в состоянии редактирования -->
    <input type="hidden" name="<?=$this->columns['id_row']?>" value="<?=$this->data[$this->columns['id_row']]?>">
    <?php endif; ?>

    <input type="hidden" name="table" value="<?=$this->table?>">

    <div class="vg-wrap vg-element vg-rows">
        <div class="vg-full vg-firm-background-color4 vg-box-shadow">

        </div>
    </div>
    <div class="vg-wrap vg-element vg-img">
        <div class="vg-full vg-firm-background-color4 vg-box-shadow">


        </div>
    </div>
    <div class="vg-wrap vg-element vg-content">

    </div>
    <div class="vg-wrap vg-element vg-full">
        <div class="vg-wrap vg-element vg-full vg-firm-background-color4 vg-box-shadow">
            <div class="vg-element vg-half vg-left">
                <div class="vg-element vg-padding-in-px">
                    <input type="submit"
                           class="vg-text vg-firm-color1 vg-firm-background-color4 vg-input vg-button"
                           value="Сохранить">
                </div>
                <div class="vg-element vg-padding-in-px">
                    <a href="/admin/shop/delete/table/shop_products/id_row/id/id/92"
                       class="vg-text vg-firm-color1 vg-firm-background-color4 vg-input vg-button vg-center vg_delete">
                        <span>Удалить</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

