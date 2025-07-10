<template>
    <div class="px-2">
        <div class="row">
            <div class="col-md-3 mt-3" v-for="student in students">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                        <img v-if="student.picture" class="profile-user-img img-fluid img-circle" :src="student.picture.path" alt="User profile picture">
                        </div>

                        <h3 class="profile-username text-center">{{student.fullname}}</h3>

                        <p class="text-muted text-center">{{student.grade}} {{ student.arm? student.arm: '' }}</p>

                        <!-- <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Followers</b> <a class="float-right">1,322</a>
                        </li>
                        <li class="list-group-item">
                            <b>Following</b> <a class="float-right">543</a>
                        </li>
                        <li class="list-group-item">
                            <b>Friends</b> <a class="float-right">13,287</a>
                        </li>
                        </ul> -->

                        <!-- <a href="#" class="btn btn-primary btn-block"><b>See more...</b></a> -->
                        <Link :href="'/parents/'+student.id" class="btn btn-primary btn-block"><b>See more</b></Link>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { Link } from '@inertiajs/inertia-vue';
    export default{
        props: ['students'],
        components: {Link},
        data(){
            return{

            }
        }
    }
</script>