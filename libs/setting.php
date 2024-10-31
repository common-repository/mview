<div class="wrap">
	<h1>mView</h1>
	<h2>食用方法</h2>
	<ol>
		<li>
			<h3>归整页面</h3>
			<ul>
				<li>
					方法一: 添加 <code>&lt;?php mView();?&gt;</code> 到自定义页面模板；
				</li>
				<li>
					方法二: 新建页面，添加短代码 <code>[mView]</code>
				</li>
			</ul>
		</li>
		<li>
			<h3>文章引用</h3>
		</li>
	</ol>
	<hr/>
	<h2>基础设置</h2>
	<form method="post" action="options.php">
		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row"><label>个人 ID</label></th>
				<td>
					<input name="mview_settings[doubanId]" id="doubanId" type="text"
					       value="<?php echo $this->settings ( 'doubanId' ); ?>" class="regular-text code">
					<p class="description">https://www.douban.com/people/<code>78108713</code>（高亮部分）</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>CDN 设置</label></th>
				<td>
					<p>
						<label title="七牛云">
							<input type="radio" name="mview_settings[cdnServer]"
							       value="qiniu" <?php if ( $this->settings ( 'cdnServer' ) == 'qiniu' ) {
								echo 'checked="checked"';
							} ?>/>
							<span>七牛云</span>
						</label>
					</p>
					<p>
						<label title="又拍云">
							<input type="radio" name="mview_settings[cdnServer]"
							       value="youpai" <?php if ( $this->settings ( 'cdnServer' ) == 'youpai' ) {
								echo 'checked="checked"';
							} ?>/>
							<span>又拍云</span>
						</label>
					</p>
					<p>
						<label title="无">
							<input type="radio"
							       name="mview_settings[cdnServer]" <?php if ( $this->settings ( 'cdnServer' ) != 'qiniu' && $this->settings ( 'cdnServer' ) != 'youpai' ) {
								echo 'checked="checked"';
							} ?>/>
							<span>无</span>
						</label>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>自定义域名</label></th>
				<td>
					<input name="mview_settings[customUrl]" id="customUrl" type="text"
					       value="<?php echo $this->settings ( 'customUrl' ); ?>" class="regular-text code">
					<p class="description">使用 CDN 此项必填, 地址需要以 http 或 https 开头</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>加载提示词</label></th>
				<td>
					<input name="mview_settings[loaderTips]" id="loaderTips" type="text"
					       value="<?php echo $this->settings ( 'loaderTips' ); ?>" class="regular-text code">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>结束提示词</label></th>
				<td>
					<input name="mview_settings[endTips]" id="endTips" type="text"
					       value="<?php echo $this->settings ( 'endTips' ); ?>" class="regular-text code">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>页面设置</label></th>
				<td>
					<p>
						<select name="pagename" id="pagename">
							<?php $pages = get_pages ( array ( 'post_type' => 'page' , 'post_status' => 'publish' ) );
							foreach ( $pages as $val ) {
								$page_id = $val->ID;
								$page_title = $val->post_title;
								$selected = ( $page_id == $this->settings ( 'pageId' ) ) ? 'selected="selected"' : "";

								echo "<option class=\"level-0\" value=\"{$page_id}\" {$selected}>{$page_title}</option>";
							}
							?>
						</select>
					</p>
					<p class="description">JavaScript、CSS 只在选中页面加载</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>JavaScript 位置</label></th>
				<td>
					<p><strong>JavaScript 位置</strong></p>
					<p>
						<label title="页面头部">
							<input type="radio" name="mview_settings[jsplace]"
							       value="0" <?php if ( $this->settings ( 'jsplace' ) == 0 ) {
								echo 'checked="checked"';
							} ?>/>
							<span>页面头部</span>
						</label>
					</p>
					<p>
						<label title="页面尾部">
							<input type="radio" name="mview_settings[jsplace]"
							       value="1" <?php if ( $this->settings ( 'jsplace' ) == 1 ) {
								echo 'checked="checked"';
							} ?>/>
							<span>页面尾部</span>
						</label>
					</p>
					<p class="description">默认 Javascript 加载在<strong>页面头部</strong>, 尾部可以加快页面加载</p>
				</td>
			</tr>
			</tbody>
		</table>
		<div>
			<?php settings_fields ( 'mview_setting_group' ); ?>
			<input type="submit" class="button-primary" name="save" value="<?php _e ( 'Save Changes' ) ?>"/>
		</div>
	</form>
	<hr/>
	<h2>支持检测</h2>
	<div>
		<ol>
			<li>缓存可写：<?php if ( is_writable ( MVIEW_PATH . '/uploads' ) ) {
					echo '√';
				} else {
					echo '×';
				}; ?></li>
			<li>数据表：<?php if ( mview_table_existed () ) {
					echo '√';
				} else {
					echo '×';
				}; ?></li>
			<li>函数支持：<strong>curl </strong><?php if ( is_callable ( 'curl_init' ) ) {
					echo '√';
				} else {
					echo '×';
				}; ?>, <strong>readfile </strong><?php if ( function_exists ( 'readfile' ) ) {
					echo '√';
				} else {
					echo '×';
				}; ?>, <strong>fopen </strong><?php if ( function_exists ( 'fopen' ) ) {
					echo '√';
				} else {
					echo '×';
				}; ?>, <strong>fwrite </strong><?php if ( function_exists ( 'fwrite' ) ) {
					echo '√';
				} else {
					echo '×';
				}; ?>, <strong>fclose </strong><?php if ( function_exists ( 'fclose' ) ) {
					echo '√';
				} else {
					echo '×';
				}; ?></li>
		</ol>
	</div>
	<hr/>
	<h2>数据概览</h2>
	<div>
		<p class="description">第一次安装或重装 <code>mView</code> 需要手动同步, 之后插件会每小时自动抓取一次。已同步：<?php echo $this->count (); ?>
			，同步数据会抓取所有数据、缓存图片，相应的会消耗一定时间。</p>
		<button type="button" id="mView-core-btn" class="button button-secondary"
		        data-id="<?php echo $this->settings ( 'doubanId' ); ?>">同步数据
		</button>
	</div>
	<script>var mView = {api: '<?php echo MVIEW_ADMIN_URL . 'admin-ajax.php'; ?>'}</script>
	<script src="<?php echo MVIEW_URL; ?>/assets/js/mviewCore-540598211b.js"></script>
</div>
