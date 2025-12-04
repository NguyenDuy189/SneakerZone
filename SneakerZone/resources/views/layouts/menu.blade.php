<ul class="category-menu">
    @foreach ($categories as $cate)
        <li>
            <a href="{{ route('category.products', $cate->slug) }}">
                {{ $cate->name }}
            </a>
        </li>
    @endforeach
</ul>
