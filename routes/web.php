<?php

use Illuminate\Support\Facades\Route;
use App\Exports\TemplateExampleExport;
use App\Models\Template;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/examples/template-excel/{template}', function (Template $template) {
    $fileName = 'template_example_' . $template->id . '.xlsx';
    return Excel::download(new TemplateExampleExport($template), $fileName);
})->name('examples.template_excel');