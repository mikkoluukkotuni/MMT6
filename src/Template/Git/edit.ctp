<div class="workinghours form large-8 medium-16 columns content float: left">
    <div class="trello-form">
        <div class="form-title">
            Github connection configuration
        </div>
        <form method="post">
            <div class="form-item">
                <div class="form-label">
                    Repository name:
                </div>
                <div class="description">
                    <input type="text" name="repository" value="<?= $git->repository ?>">
                    <div class="info-text">
                        This is the name of your repository.<br/> For example github.com/facebook/<b>react</b>
                    </div>
                </div>
            </div>
            <div class="form-item">
                <div class="form-label">
                    Owner name:
                </div>
                <div class="description">
                    <input type="text" name="owner" value="<?= $git->owner ?>">
                    <div class="info-text">
                        This is the repository owner's Github username.<br/> For example github.com/<b>facebook</b>/react
                    </div>
                </div>
            </div>
            <div class="form-submit left-space">
                <input type="submit" value="Submit" class="save" />
            </div>
        </form>
    </div>    
</div>