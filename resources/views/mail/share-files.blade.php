

<p>Hello <b>{{$user->name}}</b>, </p>
<br>
<p> User <b>{{ $author->name }}</b> shared the fallowning files to your. </p>
<hr>

    @foreach($files as $file)

        <p>{{$file->id_folder ? 'Folder' : 'File'}} - {{ $file->name }}</p>
 <br>
    @endforeach
