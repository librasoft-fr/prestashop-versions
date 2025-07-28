<nav id="wrapper_side_bar" class="{if $collapse_menu != 0}collapsed {/if}">
  <span class="menu-collapse" data-toggle-url="{$toggle_navigation_url}" style="display: none">
    <i class="material-icons rtl-flip">chevron_left</i>
    <i class="material-icons rtl-flip">chevron_left</i>
  </span>

  <div class="nav-bar-overflow">
    <wc-side-bar class="side-bar" tabs="{htmlspecialchars(array_values($tabs_simplify)|json_encode)}" />
  </div>
</nav>

<script>
    displayNavBar();
</script>
