<a href="/post/{{$post->id}}" class="list-group-item list-group-item-action">
    <img class="avatar-tiny" src="{{$post->user->avatar}}" />
    <strong>{{$post->title}}</strong> 
    @if (!isset($hideAuthor))
        <span class="small">by {{$post->user->username}}</span>
    @endif
    
    <span class="text-muted small"> - {{$post->created_at->format('n/j/Y')}}</span>
</a>