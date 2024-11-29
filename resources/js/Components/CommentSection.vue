<template>
    <div>
        <div
            v-for="comment in sortedComments"
            :key="comment.id"
            class="comment space-y-2"
        >
            <div class="mb-2 rounded bg-gray-100">
                <div class="flex items-center justify-between px-3 py-2">
                    <p>
                        {{ comment.user.name }}
                        <span class="text-sm text-gray-500">{{
                            comment.user.email
                        }}</span>
                    </p>
                    <span class="text-sm text-gray-500">{{
                        comment.created_at
                    }}</span>
                </div>
                <p class="border-t px-3 py-2">{{ comment.comment }}</p>
            </div>
        </div>
        <input
            v-model="newComment"
            @keyup.enter="postComment"
            placeholder="Write a comment..."
        />
    </div>
</template>

<script>
import dayjs from 'dayjs/esm/index.js';

export default {
    data() {
        return {
            comments: [],
            newComment: '',
        };
    },
    computed: {
        sortedComments() {
            return this.comments?.map((t) => ({
                ...t,
                created_at: dayjs(t.created_at).format('MMM d YYYY, HH:mm'),
            }));
        },
    },
    mounted() {
        console.log('Component mounted.');

        // Listen for new comments
        window.Echo.private('posts.1').listen('.CommentPosted', (event) => {
            this.comments.push(event);
        });

        // Fetch existing comments
        axios
            .get('/posts/1/comments')
            .then((response) => {
                this.comments = response.data;
            })
            .catch((error) => {
                console.log(error);
            });
    },
    methods: {
        postComment() {
            if (this.newComment.trim()) {
                axios
                    .post('/posts/1/comments', {
                        comment: this.newComment,
                    })
                    .then(() => {
                        this.newComment = ''; // Clear input after posting
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            }
        },
    },
};
</script>
