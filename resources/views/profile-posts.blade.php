<x-profile :sharedData="$sharedData" docTitle="{{$sharedData['username']}}'s Profile"> <!-- avatar="$avatar" is used to get avatar from parent component -->
  <div class="list-group">
    @foreach ($posts as $post)
      <x-post :post="$post" hideAuthor/>
    @endforeach
  </div>
</x-profile>