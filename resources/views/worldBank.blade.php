<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>World Bank ISO Lookup</title>
    {{-- Import Bootstrap CSS  --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body id="mainPageBody">
    <div class="fullView">
        <div id="searchContainer" class="container text-center">
            <h1>Please Enter an ISO Code</h1>
            <form id="isoSubmitForm" action="{{ route('search') }}" method="POST" class="d-flex">
                @csrf
                <input id="isoCode" type="text" class="form-control me-2" name="isoCode" maxlength="3" placeholder="Enter a 2 or 3 letter ISO code" required>

                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            <form id="getAllButton" action="{{ route('getAllCountries') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-secondary">Get All Countries</button>
            </form>

            {{-- If any errors exist, display them --}}
            @if ($errors->any())
                <div>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <div class="error">{{ $error }}</div>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Display the country data if available --}}
            @if (!empty($countryData) && is_array($countryData))
                <div class="row">
                    @foreach($countryData as $index => $country)
                        <!-- Restrict each row to 4 tiles by checking the iteration -->
                        @if ($index % 4 == 0)
                            @if ($index > 0)
                                </div>
                            @endif
                            <div class="countryRow row">
                      @endif
                        <div class="col-md-3">
                            <div class="countryBlock {{ $loop->last ? 'mb-5' : '' }}">
                                <h2>{{ $country['name'] }}</h2>
                                <p><strong>Region:</strong> {{ $country['region'] }}</p>
                                <p><strong>Capital City:</strong> {{ $country['capitalCity'] }}</p>
                                <p><strong>Longitude:</strong> {{ $country['longitude'] }}</p>
                                <p><strong>Latitude:</strong> {{ $country['latitude'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

{{--    <script>--}}

{{--    </script>--}}
</body>
</html>
