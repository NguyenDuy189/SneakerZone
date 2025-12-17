@auth
    <li>
        <form action="{{ route('client.logout') }}" method="POST">
            @csrf
            <button type="submit">Đăng xuất</button>
        </form>
    </li>
@else
    <li><a href="{{ route('client.login') }}">Đăng nhập</a></li>
    <li><a href="{{ route('client.register') }}">Đăng ký</a></li>
@endauth
