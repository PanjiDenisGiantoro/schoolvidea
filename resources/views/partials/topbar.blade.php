<header class="topbar d-flex">
     <!-- Sidebar Logo -->
     <div class="logo-box">
          <a href="index.html" class="logo-dark">
               <img src="assets/images/logo-sm.png" class="logo-sm" alt="logo sm">
               <img src="assets/images/logo-dark.png" class="logo-lg" alt="logo dark">
          </a>

          <a href="index.html" class="logo-light">
               <img src="assets/images/logo-sm.png" class="logo-sm" alt="logo sm">
               <img src="assets/images/logo-white.png" class="logo-lg" alt="logo light">
          </a>
     </div>

     <div class="container">
          <div class="navbar-header">

               <!-- Menu Toggle Button (sm-hover) -->
               <button type="button" class="btn btn-link d-flex button-sm-hover button-toggle-menu" aria-label="Show Full Sidebar">
                    <i class="ri-menu-2-line button-sm-hover-icon text-white"></i>
               </button>

               <div class="d-flex align-items-center gap-2">
                    <!-- App Search-->
                    <form class="app-search d-none d-md-block me-auto">
                         <div class="position-relative">
                              <input type="search" class="form-control" placeholder="Start typing..." autocomplete="off" value="">
                              <i class="ri-search-line search-widget-icon"></i>
                         </div>
                    </form>
               </div>

               <div class="d-flex align-items-center gap-2 ms-auto">
                    <!-- Theme Color (Light/Dark) -->
                    <div class="topbar-item">
                         <button type="button" class="topbar-button" id="light-dark-mode">
                              <i class="ri-moon-line fs-20 align-middle light-mode"></i>
                              <i class="ri-sun-line fs-20 align-middle dark-mode"></i>
                         </button>
                    </div>

                    <!-- Notification -->
                    <div class="dropdown topbar-item">
                         <button type="button" class="topbar-button" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="topbar-badge border border-2 border-info rounded-pill">18<span class="visually-hidden">unread messages</span></span>
                         </button>
                         <div class="dropdown-menu pt-0 dropdown-lg dropdown-menu-end" aria-labelledby="page-header-notifications-dropdown">
                              <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                                   <div class="row align-items-center">
                                        <div class="col">
                                             <h6 class="m-0 fs-16 fw-semibold"> Notifications</h6>
                                        </div>
                                   </div>
                              </div>
                              <div data-simplebar style="max-height: 280px;">
                                   <!-- Item -->
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom text-wrap">
                                        <p class="mb-0"><span class="fw-medium">Olivia Bennett</span> mentioned you in a comment <span>"This update really improves the user experience! 🚀"</span></p>
                                   </a>
                                   <!-- Item -->
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <p class="mb-0 fw-semibold">Daniel Roberts</p>
                                        <p class="mb-0 text-wrap">
                                             Just sent over the revised proposal. Let me know your thoughts.
                                        </p>
                                   </a>
                                   <!-- Item -->
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <p class="mb-0 fw-semibold">Rachel Green</p>
                                        <p class="mb-0 text-wrap">
                                             Approved your request for the new project timeline. ✅
                                        </p>
                                   </a>
                                   <!-- Item -->
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <p class="mb-0 fw-semibold text-wrap">You have <b>8</b> new project updates awaiting review.</p>
                                   </a>
                                   <!-- Item -->
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <p class="mb-0 fw-semibold">Ethan Williams</p>
                                        <p class="mb-0 text-wrap">
                                             Uploaded the latest marketing report for your review.
                                        </p>
                                   </a>

                              </div>
                         </div>
                    </div>

                    <!-- User -->
                    <div class="dropdown topbar-item">
                         <a type="button" class="topbar-button p-0" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="d-flex align-items-center gap-2">
                                   <img class="rounded-circle" width="32" src="assets/images/users/avatar-1.jpg" alt="user-image">
                                   <span class="d-lg-flex flex-column gap-1 d-none">
                                        <h5 class="my-0 fs-13 text-uppercase text-reset fw-bold">Doris Lietz</h5>
                                   </span>
                              </span>
                         </a>
                         <div class="dropdown-menu dropdown-menu-end">

                              <a class="dropdown-item" href="pages-profile.html">
                                   <i class="bx bx-user-circle fs-18 align-middle me-2"></i><span class="align-middle">My Account</span>
                              </a>

                              <a class="dropdown-item" href="pages-pricing.html">
                                   <i class="bx bx-wallet fs-18 align-middle me-2"></i><span class="align-middle">Pricing</span>
                              </a>
                              <a class="dropdown-item" href="pages-faqs.html">
                                   <i class="bx bx-help-circle fs-18 align-middle me-2"></i><span class="align-middle">Help</span>
                              </a>
                              <a class="dropdown-item" href="pages-gallery.html">
                                   <i class="bx bx-photo-album fs-18 align-middle me-2"></i>
                                   <span class="align-middle">Photos</span>
                                   <span class="align-middle float-end badge badge-soft-danger">New</span>
                              </a>

                              <div class="dropdown-divider my-1"></div>

                              <a class="dropdown-item" href="auth-lock-screen.html">
                                   <i class="bx bx-lock fs-18 align-middle me-2"></i><span class="align-middle">Lock screen</span>
                              </a>
                              <a class="dropdown-item" href="auth-signin.html">
                                   <i class="bx bx-log-out fs-18 align-middle me-2"></i><span class="align-middle">Logout</span>
                              </a>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</header>