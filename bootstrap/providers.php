<?php

use App\Providers\AppServiceProvider;
use Modules\Admin\AdminServiceProvider;
use Modules\Assessment\AssessmentServiceProvider;
use Modules\Cms\CmsServiceProvider;
use Modules\Identity\IdentityServiceProvider;
use Modules\Portal\PortalServiceProvider;

return [
    AppServiceProvider::class,
    CmsServiceProvider::class,
    IdentityServiceProvider::class,
    AssessmentServiceProvider::class,
    PortalServiceProvider::class,
    AdminServiceProvider::class,
];
