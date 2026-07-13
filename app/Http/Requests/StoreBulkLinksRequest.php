<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkLinksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $max = config('onetimelink.links.bulk_max');

        return [
            'urls' => ['required_without:csv', 'nullable', 'string', 'max:200000'],
            'csv' => ['required_without:urls', 'nullable', 'file', 'mimes:csv,txt', 'max:1024'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }

    /**
     * Merge textarea lines and CSV rows into one de-space-d list, capped
     * server-side regardless of what the client sent.
     */
    public function urlList(): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $this->input('urls', '')) ?: [];

        if ($this->hasFile('csv')) {
            $handle = fopen($this->file('csv')->getRealPath(), 'rb');
            while (($row = fgetcsv($handle)) !== false) {
                $lines[] = (string) ($row[0] ?? '');
            }
            fclose($handle);
        }

        $lines = array_values(array_filter(array_map('trim', $lines)));

        return array_slice($lines, 0, config('onetimelink.links.bulk_max'));
    }
}
