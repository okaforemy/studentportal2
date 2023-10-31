<template>
    <!-- Site wrapper -->
    <div class="wrapper">

      <!-- Content Wrapper. Contains page content -->
      <div class="mx-auto col-md-10">
        <!-- Main content -->
        <section class="content">
          <slot />
        </section>
        <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->

  
      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
      </aside>
      <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->
  </template>
  
  <script>

  import { Head } from "@inertiajs/inertia-vue";
  
  export default {
    components: {Head },
  };
  </script>