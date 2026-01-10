@auth
    <li>
        <form action="{{ route('client.logout') }}" method="POST">
            @csrf
            <button type="submit">Đăng xuất</button>
        </form>
    </li>
@else
    <li><a href="{{ route('login') }}">Đăng nhập</a></li>
    <li><a href="{{ route('register') }}">Đăng ký</a></li>
@endauth
