<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    public function test_protected_user_api_requires_a_token(): void
    {
        $this->getJson('/api/users')->assertStatus(401);
    }

    public function test_protected_datagrid_requires_a_token(): void
    {
        $this->postJson('/api/getDataGrid', [])->assertStatus(401);
    }

    public function test_company_check_validates_required_fields(): void
    {
        $this->postJson('/api/checkCompany', [])->assertStatus(422);
    }

    public function test_file_download_rejects_missing_authentication(): void
    {
        $this->getJson('/api/downloadFile/company/folder/file.txt')->assertStatus(401);
    }
}
