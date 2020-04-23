<div class="workinghours form large-8 medium-16 columns content float: left">
    <div class="trello-form">
        <div class="form-title">
            GitHub connection configuration
        </div>
        <form method="post">
            <div class="form-item">
                <div class="form-label">
                    Repository name:
                </div>
                <div class="description">
                    <input type="text" name="repository">
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
                    <input type="text" name="owner">
                    <div class="info-text">
                        This is the repository owner's GitHub username.<br/> For example github.com/<b>facebook</b>/react
                    </div>
                </div>
            </div>
            <div class="form-item">
                <div class="form-label">
                    Token:
                </div>
                <div class="description">
                    <input type="text" name="token">
                    <div class="info-text">
                        This is the repository owner's personal access token. If you don't have a token, go to 
                        <a href="https://github.com/settings/tokens/new" target="_blank">https://github.com/settings/tokens/new</a> 
                        to create one. Name it as you wish and for scope select repo. Then copy and paste the token here.
                    </div>
                </div>
            </div>
            <div class="form-submit left-space">
                <input type="submit" value="Submit" class="save" />
            </div>
        </form>
    </div>    
</div>