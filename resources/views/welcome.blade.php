<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bootstrap Form with File Upload and Table</title>
  
  <!-- Bootstrap CSS from CDN -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

  <!-- Optional: Font Awesome for icons (if needed) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

  <style>
    /* Custom CSS for spacing */
    .custom-file-control {
      margin-bottom: 10px; /* Adjust as needed */
    }
  </style>
</head>
<body>

<div class="container mt-5">
  <div class="row">
    <div class="col-md-12">

      @if ($errors->any())
        <div class="alert alert-danger t-center">
            @foreach ($errors->all() as $error)
            {{ $error }}
            @endforeach
        </div>
      @endif

      <form action="{{ route('uploadCsv') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="form-group d-flex align-items-center">
        <div class="custom-file mr-3">
          <input type="file" class="custom-file-input" id="fileUpload" onchange="updateFileName()" name="file">
          <label class="custom-file-label" for="fileUpload" id="fileLabel">Choose file</label>
          @error('file')
              <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" class="btn btn-primary">Calculate</button>
      </div>
    </form>
    </div>
  </div>

  @if(isset($csvHeader) && isset($csvData) && count($csvData) > 0)
  <div class="row mt-4">
    <div class="col-md-12">
      <div class="table-responsive table-sm">
        <table class="table table-bordered">
          <thead>
            <tr>
                @foreach($csvHeader as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
          </thead>
          <tbody>
            @foreach($csvData as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif

</div>

<!-- Bootstrap JS from CDN (for optional JavaScript functionalities) -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
  function updateFileName() {
    var fileName = document.getElementById("fileUpload").files[0].name;
    document.getElementById("fileLabel").innerText = fileName;
  }
</script>

</body>
</html>
