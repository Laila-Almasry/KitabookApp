<?php

namespace App\Http\Requests;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'         => 'required|string|max:50',
            'preview'       => 'nullable|string',
            'cover_image'    => 'nullable|image|max:2048',
            'author_id'     => 'required|exists:authors,id',
            'price'         => 'required|numeric|min:0',
            'is_physical'   => 'nullable|boolean',
            'sound_path'    => 'nullable|file|mimes:mpeg,mp3|max:51200', // optional audio file (max 50MB)
            'file_path'     => 'nullable|file|mimes:pdf,epub|max:999999', // optional file (max 50MB)
            'copies'        => 'nullable|integer|min:0',
            'category_id'   => 'required|exists:categories,id',
            'publisher'     => 'nullable|string|max:255',
            'language'      => 'required|string|max:100',
        ];
    }

    public function createBookWithCopies(): Book
    {
        $data = $this->validated();

        // Handle file uploads
        if ($this->hasFile('cover_image')) {
            $data['cover_image'] = $this->file('cover_image')->store('covers', 'public');
        }
        if ($this->hasFile('sound_path')) {
            $data['sound_path'] = $this->file('sound_path')->store('audio', 'public');
        }
        if ($this->hasFile('file_path')) {
            $data['file_path'] = $this->file('file_path')->store('books', 'public');
        }

        // Generate base barcode (main book record's barcode ends in 0)
        $baseBarcode = mt_rand(10000000000, 99999999999) . '0';
        $data['barcode'] = $baseBarcode;

        $book = Book::create($data);

        // Create book copies
        $copies = $data['copies'] ?? 0;
        for ($i = 1; $i <= $copies; $i++) {
            BookCopy::create([
                'book_id' => $book->id,
                'barcode' => $baseBarcode + $i,
                'status'  => 'available',
            ]);
        }

        return $book;
    }


public function messages(): array
    {
        return [
            'author_id.exists'    => 'The selected author does not exist.',
            'category_id.exists'  => 'The selected category does not exist.',
            'cover_image.image'    => 'The cover must be an image.',
            'sound_path.mimetypes'=> 'The sound file must be a valid audio format.',
            'file_path.mimes'     => 'The file must be a PDF or EPUB format.',
        ];
    }
}
