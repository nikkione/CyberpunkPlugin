<?php
/*
Plugin Name: Custom Meta Tags
Description: Добавляет кастомные мета-теги в раздел head вашего WordPress сайта с выбором страниц для отображения.
Version: 1.0
Author: Никита
*/

// Регистрация настроек
function cmt_register_settings()
{
  register_setting('cmt_settings_group', 'cmt_meta_tags');
}
add_action('admin_init', 'cmt_register_settings');

// Добавление страницы меню
function cmt_add_menu_page()
{
  add_menu_page(
    'Custom Meta Tags',
    'Мета Теги',
    'manage_options',
    'cmt-meta-tags',
    'cmt_settings_page',
    'dashicons-editor-code',
    100
  );
}
add_action('admin_menu', 'cmt_add_menu_page');

// Отображение страницы настроек
function cmt_settings_page()
{
?>
  <div class="wrap">
    <h1>Кастомные Мета Теги</h1>
    <form method="post" action="options.php">
      <?php
      settings_fields('cmt_settings_group');
      do_settings_sections('cmt-meta-tags');
      $meta_tags = get_option('cmt_meta_tags', []);

      // Проверка, что $meta_tags — это массив
      if (!is_array($meta_tags)) {
        $meta_tags = [];
      }
      ?>
      <table class="form-table" id="meta-tags-table">
        <tr>
          <th>Мета Тег</th>
          <th>Где показывать</th>
          <th>Действие</th>
        </tr>
        <?php foreach ($meta_tags as $index => $tag) : ?>
          <tr>
            <td>
              <input type="text" name="cmt_meta_tags[<?php echo $index; ?>][content]" value="<?php echo esc_attr(stripslashes($tag['content'])); ?>" style="width:100%;" />
            </td>
            <td>
              <select name="cmt_meta_tags[<?php echo $index; ?>][page]">
                <option value="all" <?php selected($tag['page'], 'all'); ?>>Все страницы</option>
                <option value="home" <?php selected($tag['page'], 'home'); ?>>Главная страница</option>
                <option value="single" <?php selected($tag['page'], 'single'); ?>>Посты</option>
                <option value="page" <?php selected($tag['page'], 'page'); ?>>Только страницы</option>
              </select>
            </td>
            <td>
              <button type="button" class="button remove-meta-tag">Удалить</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <button type="button" class="button" id="add-meta-tag">Добавить Мета Тег</button>
      <?php submit_button(); ?>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('add-meta-tag').addEventListener('click', function() {
        const table = document.getElementById('meta-tags-table');
        const rowCount = table.rows.length;
        const row = table.insertRow(rowCount);
        row.innerHTML = `
                    <td><input type="text" name="cmt_meta_tags[${rowCount - 1}][content]" style="width:100%;" /></td>
                    <td>
                        <select name="cmt_meta_tags[${rowCount - 1}][page]">
                            <option value="all">Все страницы</option>
                            <option value="home">Главная страница</option>
                            <option value="single">Посты</option>
                            <option value="page">Только страницы</option>
                        </select>
                    </td>
                    <td><button type="button" class="button remove-meta-tag">Удалить</button></td>
                `;
      });

      document.getElementById('meta-tags-table').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-meta-tag')) {
          e.target.closest('tr').remove();
        }
      });
    });
  </script>
<?php
}

// Вывод мета-тегов в head
function cmt_output_meta_tags()
{
  $meta_tags = get_option('cmt_meta_tags', []);

  // Проверка, что $meta_tags — это массив
  if (!is_array($meta_tags)) {
    $meta_tags = [];
  }

  // Вывод мета-тегов на основе условий
  foreach ($meta_tags as $tag) {
    if (!empty($tag['content'])) {
      // Проверка условия страницы
      if (
        $tag['page'] == 'all' ||
        ($tag['page'] == 'home' && is_front_page()) ||
        ($tag['page'] == 'single' && is_single()) ||
        ($tag['page'] == 'page' && is_page())
      ) {
        echo stripslashes($tag['content']) . "\n";
      }
    }
  }
}
add_action('wp_head', 'cmt_output_meta_tags');
