<template>
  <v-card flat>
    <v-card-title class="d-flex justify-space-between align-center">
      <v-row dense>
        <v-col cols="4">
          <span class="text-h6">Books</span>
        </v-col>

        <v-col cols="2">
          <v-text-field
            label="Filter by title"
            v-model.trim="filter.title"
            autocomplete="off"
          />
        </v-col>

        <v-col cols="2">
          <v-text-field
            label="Filter by Author"
            v-model.trim="filter.author"
            autocomplete="off"
          />
        </v-col>

        <v-col cols="2">
          <v-select
            label="Filter by Genre"
            :items=genres
            v-model.trim="filter.genre"
          ></v-select>
        </v-col>

        <v-col cols="2">
          <v-btn 
            variant="tonal" 
            :loading="loading"
            class="d-flex justify-end" 
            height="30"
            @click="loadBooks">
            Filter
          </v-btn>
        </v-col>
      </v-row>
    </v-card-title>

    <v-data-table
      class="elevation-1"
      :headers="headers"
      :items-per-page-options="[25, 50, 100]"
      :items-per-page="25"
      :items="books"
      :loading="loading"
    >
      <template #item.author="{item}">
        {{ item.author.first_name }} {{ item.author.last_name }}
      </template>

      <template #item.published_at="{item}">
        {{ moment(item.published_at).format('MMMM YYYY') }}
      </template>

      <template #item.actions="{ item }">
        <div class="d-flex flex-nowrap">

          <v-btn
            size="small"
            variant="text"
            icon="mdi-book-clock"
            color="green"
            title="Borrow this book"
            v-if=item.is_available
            @click="openDialog(item)"
          />

          <v-btn
            size="small"
            variant="text"
            icon="mdi-book-cancel"
            color="red"
            :title="'Book unavailable, return date ' + moment(item?.latest_loan?.returned_at).format('YYYY-MM-DD')"
            v-else
          />

        </div>
      </template>

      <template #loading>
        <v-sheet class="pa-4 text-center">Loading books...</v-sheet>
      </template>
    </v-data-table>

    <v-dialog persistent v-model="dialog.open" max-width="640">
      <v-card>
        <v-card-title class="text-h6">Borrow this book</v-card-title>

        <v-card-text>
          <v-form ref="authorForm" @submit.prevent="submitDialog">
            <v-row dense>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Email"
                  v-model.trim="dialog.form.email"
                  autocomplete="off"
                />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Password"
                  v-model.trim="dialog.form.password"
                  autocomplete="off"
                />
              </v-col>
              <v-col cols="12" justify="space-around">
                <v-date-picker 
                  variant="modal"
                  label="Returned at"
                  v-model.trim="dialog.form.returned_at"
                ></v-date-picker>
              </v-col>
            </v-row>

            <button type="submit" class="d-none" />
          </v-form>
        </v-card-text>

        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="closeDialog" :disabled="dialog.saving">Cancel</v-btn>
          <v-btn color="primary" :loading="dialog.saving" @click="submitDialog">
            Save
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </v-card>
</template>

<script>
import { toast } from 'vue3-toastify';
import axios from 'axios';
import moment from 'moment';
import { consoleError } from 'vuetify/lib/util/console.mjs';

export default {
  name: 'BooksTab',

  data () {
    return {
      moment,
      loading: false,
      books: [],
      genres: [],
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Title', key: 'title' },
        { title: 'Author', key: 'author', sortable: false },
        { title: 'Genre', key: 'genre' },
        { title: 'ISBN', key: 'isbn' },
        { title: 'Publish Date', key: 'published_at' },
        { title: '', key: 'actions', sortable: false, align: 'end' },
      ],
      filter: {
        title: '',
        author: '',
        genre: ''
      },
      dialog: {
        open: false,
        saving: false,
        form: {
          book_id: '',
          email: '',
          password : '',
          returned_at : '',
        },
      },
    };
  },

  methods: {
    loadGenres () {
      return axios.get('/api/v1/books/genres')
        .then(r => this.genres = r.data)
        .catch(e => {
          toast(e.response?.data?.message || e.response?.statusText || 'Error', {type: 'error'});
          console.error(e);
        })
    },
    loadBooks () {
      this.loading = true;

      return axios.get('/api/v1/books', {params: this.filter})
        .then(r => this.books = r.data)
        .catch(e => {
          toast(e.response?.data?.message || e.response?.statusText || 'Error', {type: 'error'});
          console.error(e);
        })
        .finally(() => this.loading = false);
    },
    dialogInit (form) {
      this.dialog.form = {
          book_id: '',
          email: '',
          password : '',
          returned_at : '',
        };
    },
    openDialog (book) {
      this.dialogInit();
      this.dialog.open = true;
      this.dialog.form.book_id = book.id
    },
    closeDialog () {
      this.dialog.open = false;
    },
    submitDialog () {
      if (this.dialog.saving) return;
      this.dialog.saving = true;

      const payload = {
        book_id: this.dialog.form.book_id,
        email: this.dialog.form.email,
        password : this.dialog.form.password,
        returned_at : moment(this.dialog.form.returned_at).format('YYYY-MM-DD'),
      };

      axios.post(`/api/v1/loans`, payload)
        .then(() => {
          toast('The book was borrowed', { type: 'success' });
          this.loadBooks();
          this.dialog.open = false;
        })
        .catch(e => {
          toast(e.response?.data?.message || e.response?.statusText || 'Error', { type: 'error' });
          console.error(e);
          this.dialog.saving = false;
        })
        .finally(() => this.dialog.saving = false);

    }
  },

  mounted () {
    this.loadGenres();
    this.loadBooks();
  },
};
</script>
