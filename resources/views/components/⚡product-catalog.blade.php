<?php
declare(strict_types=1);

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Product;
use App\Data\ProductData;
use App\Models\Tag;
use App\Data\ProductCollectionData;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] #[Title('List Products')] class extends Component
{
    use WithPagination;

    public $queryString = [
        'select_collections' => ['except' => []],
        'sort_by' => ['except' => 'newest'],
        'search' => ['except' => '']
    ];

    public array $select_collections = [];

    public string $search = '';

    public string $sort_by = 'newest';

    public function mount()
    {
        $this->validate();
    }

    protected function rules()
    {
        return[
            'select_collections' => 'array',
            'select_collections.*' => 'integer|exists:tags,id',
            'sort_by' => 'in:newest,latest,price-asc,price-desc',
            'search' => 'string|nullable|min:3|max:255'
        ];
    }

    public function applyFilter()
    {
        $this->validate();
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->select_collections = [];
        $this->search = '';
        $this->sort_by = 'newest';
        $this->resetErrorBag();
        $this->resetPage();
    }

    #[Computed]
    public function products(){

        if($this->getErrorBag()->isNotEmpty()){
            return ProductData::collect(
                Product::paginate(9)
            );
        }

        $query = Product::query();

        if($this->search){
            $query->where('name', 'like', "%{$this->search}%");
        }

        if(!empty($this->select_collections)){
            $query->whereHas('tags', function($query){
                $query->whereIn('tags.id', $this->select_collections);
            });
        }

        switch($this->sort_by){
            case 'latest':
                $query->oldest();
                break;
            case 'price-asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price-desc':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $products = ProductData::collect(
            $query->paginate(9)
        );

        return $products;
    }

    #[Computed]
    public function collections(){
        if($this->getErrorBag()->isNotEmpty()){
            return ProductCollectionData::collect(
                Tag::where('type', 'Collection')
                    ->withCount('products')
                    ->get()
            );
        }
        $collection_result = Tag::where('type', 'Collection')
            ->withCount('products')
            ->get();

        $collections = ProductCollectionData::collect($collection_result);

        return $collections;
    }
};
?>

<div class="container mx-auto max-w-[85rem] w-full px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 gap-10 md:grid-cols-10">
        <div class="grid grid-cols-1 gap-10 pr-6 border-r border-gray-200 md:col-span-3">
            <div>
                <div class="space-y-3">
                    <input type="text"
                        wire:model="search"
                        placeholder="Search"
                        class="@error('search') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600">
                    @error('search')
                        <div class="text-sm text-red-600">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <span class="block mt-5 mb-2 text-lg font-semibold text-gray-800 dark:text-neutral-200">
                    Collections
                </span>
                @error('select_collections.*')
                    <div class="text-sm text-red-600 mb-2">
                        {{ $message }}
                    </div>
                @enderror
                <div class="block space-y-4 @error('select_collections.*') hidden @enderror">
                    @foreach ($this->collections as $i => $item)
                        <div class="flex items-center justify-between">
                            <div class="flex">
                                <input type="checkbox"
                                    wire:model="select_collections"
                                    value="{{ $item->id }}"
                                    class="shrink-0 mt-0.5 border-gray-200 rounded-sm text-blue-600 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                    id="hs-default-checkbox-{{ $i }}">
                                <label for="hs-default-checkbox-{{ $i }}"
                                    class="text-sm font-light ms-3 dark:text-neutral-400">
                                    {{ $item->name }}
                                </label>
                            </div>
                            <span class="text-xs text-gray-800 font-loght">({{ $item->product_count }})</span>
                        </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-2 mt-10">
                    <button type="button"
                        wire:click="applyFilter"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg cursor-pointer gap-x-2 hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                        Apply Filter
                        <div wire:loading class="animate-spin inline-block size-4 border-3 border-current border-t-transparent rounded-[999px] text-muted-foreground-2" role="status" aria-label="loading">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </button>
                    <button type="button"
                        wire:click="resetFilter"
                        class="inline-flex items-center justify-center text-sm font-semibold text-blue-600 rounded-lg cursor-pointer gap-x-2 hover:text-blue-800 focus:outline-hidden focus:text-blue-800 disabled:opacity-50 disabled:pointer-events-none dark:text-blue-500 dark:hover:text-blue-400 dark:focus:text-blue-400">
                        Reset
                    </button>
                </div>
            </div>
        </div>
        <div class="col-span-1 md:col-span-7">
            <div class="flex items-center justify-between gap-5">
                <div class="font-light text-gray-800">Results: {{ $this->products->count() }} Items</div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-light text-right text-gray-800 dark:text-neutral-200">
                        Sort By :
                        @error('sort_by')
                            <div class="text-sm text-red-600">
                                {{ $message }}
                            </div>
                        @enderror
                    </span>
                    <select
                        wire:model.live="sort_by"
                        class="px-3 py-2 text-sm border-gray-200 rounded-lg pe-9 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600">
                        <option selected="">Open this select menu</option>
                        <option value="newest">Product Newst</option>
                        <option value="latest">Product Latest</option>
                        <option value="price-asc">Product Price A-Z</option>
                        <option value="price-desc">Product Price Z-A</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-5 my-5 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3">
                @forelse($this->products as $product)
                    <x-single-product-card :product="$product"/>
                @empty
                <div class="col-span-full">
                    Product Not Found
                </div>
                @endforelse
            </div>
            <div class="mt-10">
                {{ $this->products->links() }}
            </div>
        </div>
    </div>
</div>
